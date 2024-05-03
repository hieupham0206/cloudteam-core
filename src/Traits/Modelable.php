<?php

namespace Cloudteam\Core\Traits;

use Illuminate\Support\Str;

trait Modelable
{
    public function getTableNameSingularAttribute(): string
    {
        return Str::singular($this->getTable());
    }

    public function getCanBeCreatedAttribute(): bool
    {
        $name = $this->table_name_singular;

        try {
            return can("create_$name");
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getCanBeEditedAttribute(): bool
    {
        $name = $this->table_name_singular;

        try {
            return can("edit_$name");
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getCanBeDeletedAttribute(): bool
    {
        $name = $this->table_name_singular;

        try {
            return can("delete_$name");
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getCreatedAtTextAttribute()
    {
        return optional($this->created_at)->format(config('basecore.datetime_format', 'd-m-Y H:i:s'));
    }

    public function getUpdatedAtTextAttribute()
    {
        return optional($this->updated_at)->format(config('basecore.datetime_format', 'd-m-Y H:i:s'));
    }

    public function getDescriptionEvent(string $eventName): string
    {
        $displayValue = $this->{$this->displayAttribute};
        if ($displayValue) {
            $displayText = $this->label($this->displayAttribute, [], 'vi')." $displayValue ";
        }

        if ($this->logAction) {
            $eventName = $this->logAction;
        }
        $user     = auth()->user();
        $username = $user->username ?? 'System';
        $dateTime = now()->format('d-m-Y H:i:s');
        $ip       = request()->getClientIp();

        $subject  = $this->getLogName();
        $action   = "has been {$eventName} by";
        $byObject = "at $dateTime from IP $ip.";

        if ($this->logMessage) {
            return sprintf(
                '%s %s %s %s %s %s',
                $subject,
                $displayText ?? '',
                $action,
                $username,
                $byObject,
                $this->logMessage
            );
        }

        return sprintf('%s %s %s %s %s', $subject, $displayText ?? '', $action, $username, $byObject);
    }
}
