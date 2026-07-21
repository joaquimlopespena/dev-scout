<?php

namespace App\Services\Recruitment;

use App\Models\AuditLog;
use App\Models\Developer;
use App\Models\Favorite;
use App\Models\Note;
use App\Models\PipelineEntry;
use App\Models\Tag;
use App\Models\User;
use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Facades\DB;

class RecruitmentService
{
    public function __construct(private TenantContext $tenant) {}

    /** @param array{favorite?:bool,note?:string,status?:string,tags?:array<int,string>} $data */
    public function update(Developer $developer, User $actor, array $data): void
    {
        DB::transaction(function () use ($developer, $actor, $data): void {
            $organizationId = $this->tenant->organization()->id;
            if (array_key_exists('favorite', $data)) {
                Favorite::query()->where(['organization_id' => $organizationId, 'developer_id' => $developer->id])->delete();
                if ($data['favorite']) {
                    Favorite::query()->create(['organization_id' => $organizationId, 'developer_id' => $developer->id, 'created_by' => $actor->id]);
                }
            }
            if (! empty($data['note'])) {
                Note::query()->create(['organization_id' => $organizationId, 'developer_id' => $developer->id, 'author_id' => $actor->id, 'body' => $data['note']]);
            }
            if (! empty($data['status'])) {
                PipelineEntry::query()->updateOrCreate(['organization_id' => $organizationId, 'developer_id' => $developer->id], ['status' => $data['status'], 'updated_by' => $actor->id]);
            }
            if (isset($data['tags'])) {
                $tagIds = collect($data['tags'])->filter()->map(fn ($name) => Tag::query()->firstOrCreate(['organization_id' => $organizationId, 'name' => $name])->id);
                DB::table('developer_tag')->where('developer_id', $developer->id)->whereIn('tag_id', Tag::query()->where('organization_id', $organizationId)->select('id'))->delete();
                foreach ($tagIds as $tagId) {
                    DB::table('developer_tag')->insertOrIgnore(['developer_id' => $developer->id, 'tag_id' => $tagId]);
                }
            }
            AuditLog::query()->create(['organization_id' => $organizationId, 'actor_id' => $actor->id, 'action' => 'developer.recruitment_updated', 'auditable_type' => $developer->getMorphClass(), 'auditable_id' => $developer->id, 'metadata' => ['fields' => array_keys($data)]]);
        });
    }
}
