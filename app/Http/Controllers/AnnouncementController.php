<?php

namespace App\Http\Controllers;

use App\Enums\AnnouncementStatus;
use App\Models\Announcement;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class AnnouncementController extends Controller
{
    public function adminIndex(Request $request): JsonResponse
    {
        $query = Announcement::query()->orderByDesc('created_at');

        if ($request->filled('status')) {
            $status = AnnouncementStatus::tryFrom($request->string('status')->toString());

            if (!$status) {
                throw ValidationException::withMessages([
                    'status' => ['The provided status is invalid.'],
                ]);
            }

            $query->where('status', $status);
        }

        return response()->json($query->get());
    }

    public function show(Announcement $announcement): JsonResponse
    {
        return response()->json($announcement);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validateAnnouncement($request);

        $publishingDate = $this->parseNullableDate($data['publishing_date'] ?? null);
        $disappearingDate = $this->parseNullableDate($data['disappearing_date'] ?? null);

        $this->assertValidDateWindow($publishingDate, $disappearingDate);

        $announcement = Announcement::create([
            'title' => $data['title'],
            'content' => $data['content'],
            'status' => AnnouncementStatus::DRAFT,
            'publishing_date' => $publishingDate,
            'disappearing_date' => $disappearingDate,
            'filters' => $this->normalizeFilters($data['filters'] ?? null),
        ]);

        return response()->json($announcement, 201);
    }

    public function update(Request $request, Announcement $announcement): JsonResponse
    {
        $data = $this->validateAnnouncement($request, true);

        $updates = [];
        $now = Carbon::now();

        if (array_key_exists('title', $data)) {
            $updates['title'] = $data['title'];
        }

        if (array_key_exists('content', $data)) {
            $updates['content'] = $data['content'];
        }

        $publishingDate = array_key_exists('publishing_date', $data)
            ? $this->parseNullableDate($data['publishing_date'])
            : $announcement->publishing_date;

        $disappearingDate = array_key_exists('disappearing_date', $data)
            ? $this->parseNullableDate($data['disappearing_date'])
            : $announcement->disappearing_date;

        $this->assertValidDateWindow($publishingDate, $disappearingDate);

        if (array_key_exists('publishing_date', $data)) {
            $updates['publishing_date'] = $publishingDate;
        }

        if (array_key_exists('disappearing_date', $data)) {
            $updates['disappearing_date'] = $disappearingDate;

            if ($disappearingDate && $disappearingDate->lt($now)) {
                $updates['status'] = AnnouncementStatus::EXPIRED;
            }
        }

        if (array_key_exists('filters', $data)) {
            $updates['filters'] = $this->normalizeFilters($data['filters']);
        }

        if (!empty($updates)) {
            $announcement->fill($updates)->save();
        }

        return response()->json($announcement->refresh());
    }

    public function destroy(Announcement $announcement): JsonResponse
    {
        $announcement->delete();

        return response()->json([
            'message' => 'Announcement deleted successfully.',
        ]);
    }

    public function publish(Request $request, Announcement $announcement): JsonResponse
    {
        if ($announcement->status === AnnouncementStatus::PUBLISHED) {
            throw ValidationException::withMessages([
                'announcement' => ['The announcement is already published.'],
            ]);
        }

        $data = $this->validatePublish($request);

        $publishingDate = $this->parseNullableDate($data['publishing_date'] ?? null) ?? Carbon::now();
        $disappearingDate = array_key_exists('disappearing_date', $data)
            ? $this->parseNullableDate($data['disappearing_date'])
            : $announcement->disappearing_date;

        $this->assertValidDateWindow($publishingDate, $disappearingDate);

        $updates = [
            'status' => AnnouncementStatus::PUBLISHED,
            'publishing_date' => $publishingDate,
            'disappearing_date' => $disappearingDate,
        ];

        if (array_key_exists('filters', $data)) {
            $updates['filters'] = $this->normalizeFilters($data['filters']);
        }

        $announcement->fill($updates)->save();

        return response()->json($announcement->refresh());
    }

    public function republish(Request $request, Announcement $announcement): JsonResponse
    {
        if ($announcement->status !== AnnouncementStatus::PUBLISHED) {
            throw ValidationException::withMessages([
                'announcement' => ['Only published announcements can be re-published.'],
            ]);
        }

        $data = $this->validatePublish($request);

        $publishingDate = $this->parseNullableDate($data['publishing_date'] ?? null) ?? Carbon::now();
        $disappearingDate = array_key_exists('disappearing_date', $data)
            ? $this->parseNullableDate($data['disappearing_date'])
            : $announcement->disappearing_date;

        $this->assertValidDateWindow($publishingDate, $disappearingDate);

        $updates = [
            'publishing_date' => $publishingDate,
            'disappearing_date' => $disappearingDate,
        ];

        if (array_key_exists('filters', $data)) {
            $updates['filters'] = $this->normalizeFilters($data['filters']);
        }

        $announcement->fill($updates)->save();

        return response()->json($announcement->refresh());
    }

    public function active(Request $request): JsonResponse
    {
        $user = $request->user();

        $student = Student::with('approvedApplication')
            ->where('user_id', $user->user_id)
            ->first();

        $announcements = Announcement::active()
            ->get()
            ->filter(fn(Announcement $announcement) => $this->announcementVisibleToStudent($announcement, $student))
            ->values();

        return response()->json($announcements);
    }

    private function validateAnnouncement(Request $request, bool $isUpdate = false): array
    {
        $rules = [
            'title' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255'],
            'content' => [$isUpdate ? 'sometimes' : 'required', 'string'],
            'publishing_date' => [$isUpdate ? 'sometimes' : 'nullable', 'date'],
            'disappearing_date' => [$isUpdate ? 'sometimes' : 'nullable', 'date'],
            'filters' => [$isUpdate ? 'sometimes' : 'nullable', 'array'],
            'filters.scholarships' => ['sometimes', 'array'],
            'filters.scholarships.*' => ['integer', 'exists:scholarships,scholarship_id'],
            'filters.countries' => ['sometimes', 'array'],
            'filters.countries.*' => ['integer', 'exists:countries,country_id'],
            'filters.universities' => ['sometimes', 'array'],
            'filters.universities.*' => ['integer', 'exists:universities,university_id'],
        ];

        return $request->validate($rules);
    }

    private function validatePublish(Request $request): array
    {
        return $request->validate([
            'publishing_date' => ['nullable', 'date'],
            'disappearing_date' => ['nullable', 'date'],
            'filters' => ['sometimes', 'array'],
            'filters.scholarships' => ['sometimes', 'array'],
            'filters.scholarships.*' => ['integer', 'exists:scholarships,scholarship_id'],
            'filters.countries' => ['sometimes', 'array'],
            'filters.countries.*' => ['integer', 'exists:countries,country_id'],
            'filters.universities' => ['sometimes', 'array'],
            'filters.universities.*' => ['integer', 'exists:universities,university_id'],
        ]);
    }

    private function parseNullableDate(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        return Carbon::parse($value);
    }

    private function assertValidDateWindow(?Carbon $publishingDate, ?Carbon $disappearingDate): void
    {
        if ($publishingDate && $disappearingDate && $disappearingDate->lte($publishingDate)) {
            throw ValidationException::withMessages([
                'disappearing_date' => ['The disappearing date must be after the publishing date.'],
            ]);
        }
    }

    private function normalizeFilters(?array $filters): array
    {
        $filters = $filters ?? [];

        return [
            'scholarships' => $this->sanitizeIdArray($filters['scholarships'] ?? []),
            'countries' => $this->sanitizeIdArray($filters['countries'] ?? []),
            'universities' => $this->sanitizeIdArray($filters['universities'] ?? []),
        ];
    }

    private function sanitizeIdArray(array $values): array
    {
        $values = array_filter($values, fn($value) => is_numeric($value));
        $values = array_map('intval', $values);

        return array_values(array_unique($values));
    }

    private function announcementVisibleToStudent(Announcement $announcement, ?Student $student): bool
    {
        $filters = $announcement->filters ?? [];

        $scholarships = $this->sanitizeIdArray($filters['scholarships'] ?? []);
        $countries = $this->sanitizeIdArray($filters['countries'] ?? []);
        $universities = $this->sanitizeIdArray($filters['universities'] ?? []);

        if (!$student) {
            return false;
        }

        $studentScholarshipId = $student->approvedApplication?->scholarship_id;
        $studentCountryId = $student->country_id;
        $studentUniversityId = $student->university_id;

        $matchesScholarship = empty($scholarships)
            || ($studentScholarshipId !== null && in_array($studentScholarshipId, $scholarships, true));

        if (!$matchesScholarship) {
            return false;
        }

        $matchesCountry = empty($countries)
            || ($studentCountryId !== null && in_array($studentCountryId, $countries, true));

        if (!$matchesCountry) {
            return false;
        }

        $matchesUniversity = empty($universities)
            || ($studentUniversityId !== null && in_array($studentUniversityId, $universities, true));

        return $matchesUniversity;
    }
}
