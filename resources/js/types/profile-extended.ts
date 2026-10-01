export type ProfileExtendedData = {
    headline: string | null;
    bio: string | null;
    birthDate: string | null;
    nationalCode: string | null;
    provinceCityId: number | null;
    provinceId: number | null;
    cityTitle: string | null;
    provinceTitle: string | null;
    isPublic: boolean;
    isVerified: boolean;
};

export type LocationOptions = {
    provinces: { id: number; title: string }[];
    cities: { id: number; title: string; provinceId: number }[];
};

export type ProfileExtendedFormData = {
    headline: string;
    bio: string;
    birth_date: string;
    national_code: string;
    province_city_id: string;
    is_public: boolean;
};

export function extendedToFormDefaults(extended: ProfileExtendedData): ProfileExtendedFormData {
    return {
        headline: extended.headline ?? '',
        bio: extended.bio ?? '',
        birth_date: extended.birthDate ?? '',
        national_code: extended.nationalCode ?? '',
        province_city_id: extended.provinceCityId ? String(extended.provinceCityId) : '',
        is_public: extended.isPublic ?? true,
    };
}
