<?php

namespace App\Modules\AdobeConnect\Services;

use App\Modules\AdobeConnect\Enums\AdobeRoomAccessType;
use App\Modules\AdobeConnect\Enums\AdobeRoomStatus;
use App\Modules\AdobeConnect\Models\AdobeRoom;
use App\Modules\AdobeConnect\Support\AdobeConnectConfig;
use App\Modules\AdobeConnect\Support\AdobeConnectUrlBuilder;
use App\Modules\Courses\Enums\CourseClassDeliveryType;
use App\Modules\Courses\Enums\CourseClassOnlinePlatform;
use App\Modules\Courses\Models\CourseClass;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Throwable;

class ProvisionCourseClassAdobeMeetingService
{
    public function __construct(
        private readonly AdobeConnectConfig $config,
        private readonly AdobeConnectApi $api,
        private readonly AdobeConnectUrlBuilder $urls,
    ) {}

    public function provisionIfNeeded(CourseClass $courseClass): ?AdobeRoom
    {
        if (! $this->shouldAutoProvision($courseClass)) {
            return null;
        }

        $courseClass->loadMissing(['course', 'classLevelAdobeRooms']);

        if ($courseClass->classLevelAdobeRooms()->active()->exists()) {
            return null;
        }

        $courseClass->loadMissing('course');

        $name = $this->meetingName($courseClass);
        [$begin, $end] = $this->meetingWindow($courseClass);

        try {
            $created = $this->api->createMeeting(
                $name,
                $begin,
                $end,
                $this->optionalUrlPath($courseClass),
            );
        } catch (Throwable $first) {
            if ($this->optionalUrlPath($courseClass) === null) {
                throw $first;
            }

            $created = $this->api->createMeeting($name, $begin, $end);
        }

        $urlPath = trim($created['url_path']);

        return AdobeRoom::query()->create([
            'course_class_id' => $courseClass->id,
            'class_session_id' => null,
            'sco_id' => $created['sco_id'],
            'url_path' => $urlPath,
            'meeting_url' => $this->urls->meetingJoinUrl($urlPath),
            'access_type' => AdobeRoomAccessType::Protected,
            'passcode' => null,
            'status' => AdobeRoomStatus::Active,
            'last_synced_at' => now(),
        ]);
    }

    private function shouldAutoProvision(CourseClass $courseClass): bool
    {
        if (! $this->config->autoProvisionMeetings() || ! $this->config->isApiConfigured()) {
            return false;
        }

        $platform = $courseClass->online_platform ?? CourseClassOnlinePlatform::AdobeConnect;
        if ($platform !== CourseClassOnlinePlatform::AdobeConnect) {
            return false;
        }

        $delivery = $courseClass->delivery_type ?? CourseClassDeliveryType::Online;
        if (! in_array($delivery, [CourseClassDeliveryType::Online, CourseClassDeliveryType::Hybrid], true)) {
            return false;
        }

        return true;
    }

    private function meetingName(CourseClass $courseClass): string
    {
        $courseTitle = trim((string) ($courseClass->course?->title ?? ''));
        $className = trim((string) $courseClass->name);
        $code = trim((string) $courseClass->code);

        $parts = array_values(array_filter([$courseTitle, $className, $code !== '' ? "({$code})" : '']));

        $name = $parts !== [] ? implode(' — ', $parts) : 'Class Meeting';

        return Str::limit($name, 120, '');
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function meetingWindow(CourseClass $courseClass): array
    {
        $begin = $courseClass->starts_at ?? $courseClass->registration_starts_at;
        $end = $courseClass->ends_at ?? $courseClass->registration_ends_at;

        if ($begin === null) {
            $begin = now()->addHour();
        }

        if ($end === null || $end->lte($begin)) {
            $end = $begin->copy()->addMonths(6);
        }

        return [$begin, $end];
    }

    private function optionalUrlPath(CourseClass $courseClass): ?string
    {
        $code = Str::slug((string) $courseClass->code, '-');
        if ($code === '') {
            return null;
        }

        return 'academy-class-'.$courseClass->id.'-'.$code;
    }
}
