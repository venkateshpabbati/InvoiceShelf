<?php

namespace App\Domains\Metadata\Models;

use App\Domains\Accounts\Models\Company;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * One record's answer to one custom field.
 *
 * The row names the definition it answers and the record that owns it -- a
 * customer, a document, a line on a document, a payment, an expense -- and
 * keeps the answer in the column the definition's input type maps to.
 *
 * The type name is copied onto the row rather than read from the definition,
 * so an answer can be rendered without loading the definition, and so
 * changing a definition's input type later leaves answers already on file
 * reading the column they were written to.
 */
class CustomFieldValue extends Model
{
    protected $table = 'custom_field_values';

    use HasFactory;

    /**
     * Everything but the key may be mass assigned.
     *
     * @var array
     */
    protected $guarded = [
        'id',
    ];

    /**
     * Inert. The framework stopped reading this property, so the two columns
     * named here are handed out as the plain strings the driver returns, not
     * as date objects. Kept because it is part of the class as published.
     *
     * @var array
     */
    protected $dates = [
        'date_answer',
        'date_time_answer',
    ];

    /**
     * The answer travels with every serialized row, under the camel-cased
     * name it is computed from.
     *
     * @var array
     */
    protected $appends = [
        'defaultAnswer',
    ];

    /**
     * Reduce a time-of-day answer to H:i:s.
     *
     * Unlike the definition's copy of this mutator, an empty value is written
     * through as null instead of being dropped, so an answer here can be
     * cleared. A value the parser cannot read becomes midnight.
     */
    public function setTimeAnswerAttribute(mixed $value): void
    {
        $this->attributes['time_answer'] = $value ? date('H:i:s', strtotime($value)) : null;
    }

    /**
     * The answer, read from the column this row's type maps to. A type
     * outside the mapping reads the string column.
     *
     * The name is inherited from the definition the row was stamped from:
     * there is nothing "default" about an answer a record actually gave.
     */
    public function getDefaultAnswerAttribute()
    {
        $answerColumn = getCustomFieldValueKey($this->type);

        return $this->{$answerColumn};
    }

    /**
     * The company the answer was stamped with.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /**
     * The definition this answers.
     */
    public function customField(): BelongsTo
    {
        return $this->belongsTo(CustomField::class, 'custom_field_id');
    }

    /**
     * The record the answer belongs to, whatever type it is.
     */
    public function customFieldValuable(): MorphTo
    {
        return $this->morphTo();
    }
}
