<?php

namespace App\Modules\Assessments\Support;

use App\Modules\Assessments\Models\Certificate;

class CertificateSeo
{
    /**
     * @return array{title: string, description: string, canonical: string, keywords: string, og_image: string|null, og_type: string}
     */
    public static function meta(Certificate $certificate): array
    {
        $canonical = route('certificates.verify', ['code' => $certificate->code]);

        return [
            'title' => 'اعتبارسنجی گواهینامه '.$certificate->code,
            'description' => 'بررسی اعتبار گواهینامه «'.$certificate->title.'»',
            'canonical' => $canonical,
            'keywords' => '',
            'og_image' => null,
            'og_type' => 'website',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function credentialJsonLd(Certificate $certificate, string $appName): array
    {
        $certificate->loadMissing(['course', 'user']);
        $seo = self::meta($certificate);
        $studentName = trim(($certificate->user?->name ?? '').' '.($certificate->user?->family ?? ''));

        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'EducationalOccupationalCredential',
            'name' => $certificate->title,
            'credentialCategory' => 'certificate',
            'url' => $seo['canonical'],
            'identifier' => $certificate->code,
            'recognizedBy' => [
                '@type' => 'Organization',
                'name' => $appName,
                'url' => url('/'),
            ],
        ];

        if ($studentName !== '') {
            $data['credentialSubject'] = [
                '@type' => 'Person',
                'name' => $studentName,
            ];
        }

        if ($certificate->course) {
            $data['about'] = [
                '@type' => 'Course',
                'name' => $certificate->course->title,
                'url' => url('/courses/'.$certificate->course->publicSlug()),
            ];
        }

        if ($certificate->issued_at) {
            $data['dateCreated'] = $certificate->issued_at->toDateString();
        }

        return $data;
    }
}
