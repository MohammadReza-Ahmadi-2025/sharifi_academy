<?php

namespace App\Modules\AdobeConnect\Services;

use App\Models\User;
use App\Modules\AdobeConnect\Support\AdobeConnectConfig;
use App\Modules\AdobeConnect\Support\AdobeConnectSessionSco;
use App\Modules\AdobeConnect\Support\AdobeConnectUserCredentials;
use App\Modules\Courses\Enums\EnrollmentStatus;
use App\Modules\Courses\Enums\SessionAttendanceRole;
use App\Modules\Courses\Enums\SessionAttendanceSource;
use App\Modules\Courses\Enums\SessionAttendanceStatus;
use App\Modules\Courses\Models\ClassSession;
use App\Modules\Courses\Models\SessionAttendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class SyncAdobeSessionAttendanceService
{
    public function __construct(
        private readonly AdobeConnectConfig $config,
        private readonly AdobeConnectApi $api,
    ) {}

    /**
     * @return array{present: int, absent: int, skipped: int, matched_rows: int, sco_id: string}
     */
    public function sync(ClassSession $classSession, bool $markAbsent = true): array
    {
        if (! $this->config->isApiConfigured()) {
            throw ValidationException::withMessages([
                'adobe' => 'اتصال Adobe Connect در .env تنظیم نشده است.',
            ]);
        }

        $classSession->loadMissing(['courseClass', 'adobeRooms', 'courseClass.classLevelAdobeRooms']);
        $courseClass = $classSession->courseClass;
        if ($courseClass === null || ! $courseClass->usesAdobeConnect()) {
            throw ValidationException::withMessages([
                'adobe' => 'این جلسه روی Adobe Connect نیست.',
            ]);
        }

        $scoId = AdobeConnectSessionSco::meetingScoId($classSession);
        if ($scoId === null) {
            throw ValidationException::withMessages([
                'adobe' => 'اتاق Adobe برای این جلسه/کلاس تنظیم نشده است.',
            ]);
        }

        $filters = $this->dateFiltersForSession($classSession);

        try {
            $rows = $this->api->meetingAttendance($scoId, $filters);
        } catch (Throwable $exception) {
            Log::warning('Adobe Connect attendance report failed', [
                'class_session_id' => $classSession->id,
                'sco_id' => $scoId,
                'message' => $exception->getMessage(),
            ]);

            throw ValidationException::withMessages([
                'adobe' => 'دریافت گزارش حضور از Adobe ناموفق بود: '.$exception->getMessage(),
            ]);
        }

        $aggregated = $this->aggregateRows($rows);
        $enrolledUsers = $courseClass->enrollments()
            ->where('status', EnrollmentStatus::Confirmed)
            ->with('user')
            ->get()
            ->pluck('user')
            ->filter();

        $stats = [
            'present' => 0,
            'absent' => 0,
            'skipped' => 0,
            'matched_rows' => count($aggregated),
            'sco_id' => $scoId,
        ];

        foreach ($enrolledUsers as $user) {
            if (! $user instanceof User) {
                continue;
            }

            $match = $this->matchAggregatedForUser($user, $aggregated);
            $existing = SessionAttendance::query()
                ->where('class_session_id', $classSession->id)
                ->where('user_id', $user->id)
                ->first();

            if ($match !== null) {
                SessionAttendance::query()->updateOrCreate(
                    [
                        'class_session_id' => $classSession->id,
                        'user_id' => $user->id,
                    ],
                    [
                        'status' => SessionAttendanceStatus::Present,
                        'role' => SessionAttendanceRole::Student,
                        'first_joined_at' => $match['first_joined_at'],
                        'last_left_at' => $match['last_left_at'],
                        'duration_minutes' => $match['duration_minutes'],
                        'source' => SessionAttendanceSource::System,
                        'note' => $existing?->note,
                    ],
                );
                $stats['present']++;

                continue;
            }

            if ($existing?->status === SessionAttendanceStatus::Excused) {
                $stats['skipped']++;

                continue;
            }

            if (! $markAbsent) {
                $stats['skipped']++;

                continue;
            }

            SessionAttendance::query()->updateOrCreate(
                [
                    'class_session_id' => $classSession->id,
                    'user_id' => $user->id,
                ],
                [
                    'status' => SessionAttendanceStatus::Absent,
                    'role' => SessionAttendanceRole::Student,
                    'first_joined_at' => null,
                    'last_left_at' => null,
                    'duration_minutes' => 0,
                    'source' => SessionAttendanceSource::System,
                    'note' => $existing?->note,
                ],
            );
            $stats['absent']++;
        }

        return $stats;
    }

    /**
     * @return array{date_gte?: string, date_lte?: string}
     */
    private function dateFiltersForSession(ClassSession $session): array
    {
        $filters = [];

        if ($session->scheduled_start_at !== null) {
            $filters['date_gte'] = $session->scheduled_start_at->copy()->subHours(3)->toIso8601String();
        }

        if ($session->scheduled_end_at !== null) {
            $filters['date_lte'] = $session->scheduled_end_at->copy()->addHours(3)->toIso8601String();
        } elseif ($session->scheduled_start_at !== null) {
            $filters['date_lte'] = $session->scheduled_start_at->copy()->addHours(8)->toIso8601String();
        }

        return $filters;
    }

    /**
     * @param  list<array{principal_id: string, login: string, participant_name: string, date_created: string|null, date_end: string|null}>  $rows
     * @return array<string, array{principal_id: string, login: string, first_joined_at: ?Carbon, last_left_at: ?Carbon, duration_minutes: int}>
     */
    private function aggregateRows(array $rows): array
    {
        $aggregated = [];

        foreach ($rows as $row) {
            $key = $row['principal_id'] !== ''
                ? 'p:'.$row['principal_id']
                : 'l:'.mb_strtolower($row['login']);

            $start = $this->parseAdobeDate($row['date_created']);
            $end = $this->parseAdobeDate($row['date_end']);
            $minutes = 0;
            if ($start !== null && $end !== null && $end->greaterThan($start)) {
                $minutes = (int) max(1, $start->diffInMinutes($end));
            }

            if (! isset($aggregated[$key])) {
                $aggregated[$key] = [
                    'principal_id' => $row['principal_id'],
                    'login' => $row['login'],
                    'first_joined_at' => $start,
                    'last_left_at' => $end,
                    'duration_minutes' => $minutes,
                ];

                continue;
            }

            $current = $aggregated[$key];
            if ($start !== null && ($current['first_joined_at'] === null || $start->lessThan($current['first_joined_at']))) {
                $current['first_joined_at'] = $start;
            }
            if ($end !== null && ($current['last_left_at'] === null || $end->greaterThan($current['last_left_at']))) {
                $current['last_left_at'] = $end;
            }
            $current['duration_minutes'] += $minutes;
            if ($row['login'] !== '' && $current['login'] === '') {
                $current['login'] = $row['login'];
            }
            $aggregated[$key] = $current;
        }

        return $aggregated;
    }

    /**
     * @param  array<string, array{principal_id: string, login: string, first_joined_at: ?Carbon, last_left_at: ?Carbon, duration_minutes: int}>  $aggregated
     * @return array{principal_id: string, login: string, first_joined_at: ?Carbon, last_left_at: ?Carbon, duration_minutes: int}|null
     */
    private function matchAggregatedForUser(User $user, array $aggregated): ?array
    {
        $principalId = trim((string) ($user->adobe_connect_principal_id ?? ''));
        if ($principalId !== '' && isset($aggregated['p:'.$principalId])) {
            return $aggregated['p:'.$principalId];
        }

        $credentials = AdobeConnectUserCredentials::forUser($user);
        if ($credentials !== null) {
            $loginKey = 'l:'.mb_strtolower($credentials->login);
            if (isset($aggregated[$loginKey])) {
                return $aggregated[$loginKey];
            }

            foreach ($aggregated as $row) {
                if (mb_strtolower($row['login']) === mb_strtolower($credentials->login)) {
                    return $row;
                }
            }
        }

        return null;
    }

    private function parseAdobeDate(?string $value): ?Carbon
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (Throwable) {
            return null;
        }
    }
}
