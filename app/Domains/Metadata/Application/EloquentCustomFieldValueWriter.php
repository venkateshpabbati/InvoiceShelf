<?php

namespace App\Domains\Metadata\Application;

use App\Domains\Metadata\Contracts\CustomFieldValueWriter;
use App\Domains\Metadata\Models\CustomField;
use Illuminate\Database\Eloquent\Model;

class EloquentCustomFieldValueWriter implements CustomFieldValueWriter
{
    public function attach(Model $valuable, iterable $customFields): void
    {
        foreach ($customFields as $field) {
            $field = $this->normalize($field);
            $customField = CustomField::find($field['id']);
            $answerColumn = getCustomFieldValueKey($customField->type);

            $valuable->fields()->create([
                'type' => $customField['type'],
                'custom_field_id' => $customField['id'],
                'company_id' => $customField['company_id'],
                $answerColumn => $field['value'],
            ]);
        }
    }

    public function update(Model $valuable, iterable $customFields): void
    {
        foreach ($customFields as $field) {
            $field = $this->normalize($field);
            $customField = CustomField::find($field['id']);
            $customFieldValue = $valuable->fields()->firstOrCreate([
                'custom_field_id' => $customField['id'],
                'type' => $customField['type'],
                'company_id' => $valuable->company_id,
            ]);

            $answerColumn = getCustomFieldValueKey($customField->type);
            $customFieldValue->forceFill([$answerColumn => $field['value']])->save();
        }
    }

    /** @return array{id: int, value: mixed} */
    private function normalize(mixed $field): array
    {
        return is_array($field) ? $field : (array) $field;
    }
}
