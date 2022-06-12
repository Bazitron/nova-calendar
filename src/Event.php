<?php

/*
 * © Copyright 2022 · Willem Vervuurt, Studio Delfuego
 * 
 * You can modify, use and distribute this package under one of two licenses:
 * 1. GNU AGPLv3
 * 2. A perpetual, non-revocable and 100% free (as in beer) do-what-you-want 
 *    license that allows both non-commercial and commercial use, under conditions.
 *    See LICENSE.md for details.
 * 
 *    (it boils down to: do what you want as long as you're building and/or
 *     using calendar views, but don't embed this package or a modified version
 *     of it in free or paid-for software libraries and packages aimed at developers).
 */
 
namespace Wdelfuego\NovaCalendar;

use DateTimeInterface;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Laravel\Nova\Resource as NovaResource;

class Event
{
    public static function fromResource(NovaResource $resource, string $dateAttributeStart, string $dateAttributeEnd = null) : self
    {
        return is_null($dateAttributeEnd)
            ? (new self($resource->title(), $resource->resource->$dateAttributeStart))->withResource($resource)
            : (new self($resource->title(), $resource->resource->$dateAttributeStart, $resource->resource->$dateAttributeEnd))->withResource($resource);
    }
            
    protected $name;
    protected $start;
    protected $end;
    protected $notes;
    protected $badges;
    
    protected $novaResource = null;
    protected $displayTime = false;
    protected $url = null;
    protected $style = null;
    protected $timeFormat = 'H:i';
    
    public function __construct(
        string $name, 
        DateTimeInterface $start,
        DateTimeInterface $end = null, 
        string $notes = '', 
        array $badges = [])
    {
        $this->name = $name;
        $this->start = $start;
        $this->end = $end;
        $this->notes = $notes;
        $this->badges = $badges;
    }

    public function toArray(Carbon $displayDate, int $firstDayOfWeek) : array
    {
        return [
//            'name' => CalendarDay::weekdayColumn($displayDate, $firstDayOfWeek) .'-' .($this->end() ? $displayDate->diffInDays($this->end) : 0) .$this->name,
            'name' => $this->name,
            'start_date' => $this->start->format("Y-m-d"),
            'start_time' => $this->start->format($this->timeFormat),
            'end_date' => $this->end ? $this->end->format("Y-m-d") : null,
            'end_time' => $this->end ? $this->end->format($this->timeFormat) : null,
            'single_day' => $this->isSingleDayEvent() ? 1 : 0,
            'spans_days' => min($this->spansDaysFrom($displayDate), 7),
            'starts_event' => $this->startsEvent($displayDate) ? 1 : 0,
            'ends_event' => $this->endsEvent($displayDate, $firstDayOfWeek) ? 1 : 0,
            'notes' => $this->notes,
            'badges' => $this->badges,
            'url' => $this->url,
            'options' => [
                'style' => $this->style,
                'displayTime' => $this->displayTime ? 1 : 0,
            ],
        ];
    }
    
    public function isSingleDayEvent() : bool
    {
        return !$this->end || $this->end->isSameDay($this->start);
    }
    
    public function spansDaysFrom(Carbon $displayDate) : int
    {
        $out = 1;
        if($this->end)
        {
            $out += $displayDate->diffInDays($this->end);
        }
        return $out;
    }

    public function startsEvent(Carbon $displayDate)
    {
        return $this->start->isSameDay($displayDate);
    }
        
    public function endsEvent(Carbon $displayDate, int $firstDayOfWeek)
    {
        $daysLeft = $this->end() ? $displayDate->diffInDays($this->end) : 0;
        return $daysLeft <= 7 - CalendarDay::weekdayColumn($displayDate, $firstDayOfWeek);
    }
        
    public function resource(NovaResource $v = null) : ?NovaResource
    {
        if(!is_null($v))
        {
            $this->novaResource = $v;
        }
        
        return $this->novaResource;
    }
    
    public function withResource(NovaResource $v) : self
    {
        $this->resource($v);
        return $this;
    }
    
    public function hasNovaResource(string $class = null) : bool
    {
        if(is_null($class))
        {
            return !is_null($this->novaResource);
        }

        return ($this->novaResource instanceof $class);
    }
    
    public function style(string $v = null)
    {
        if(!is_null($v))
        {
            $this->style = $v;
        }
        
        return $this->style;
    }
    
    public function withStyle(string $v)
    {
        $this->style($v);
        return $this;
    }
    
    
    public function timeFormat(string $v = null)
    {
        if(!is_null($v))
        {
            $this->timeFormat = $v;
        }
        
        return $this->timeFormat;
    }
    
    public function withTimeFormat(string $v)
    {
        $this->timeFormat($v);
        return $this;
    }
    
    public function url(string $v = null)
    {
        if(!is_null($v))
        {
            $this->url = $v;
        }
        
        return $this->url;
    }
    
    public function withUrl(string $v)
    {
        $this->url($v);
        return $this;
    }
    
    public function displayTime(bool $v = true)
    {
        $this->displayTime = $v;
        return $this;
    }
    
    public function hideTime()
    {
        return $this->displayTime(false);
    }
    
    public function model() : ?EloquentModel
    {
        return $this->novaResource ? $this->novaResource->resource : null;
    }

    public function name(string $v = null) : string
    {
        if(!is_null($v)) 
        {
            $this->name = $v;
        }
        
        return $this->name;
    }
    
    public function withName(string $v) : self
    {
        $this->name($v);
        return $this;
    }
    
    public function start(DateTimeInterface $v = null) : DateTimeInterface
    {
        if(!is_null($v)) {
            $this->start = $v;
        }
        
        return $this->start;
    }
    
    public function withStart(DateTimeInterface $v) : self
    {
        $this->start($v);
        return $this;
    }

    public function end(DateTimeInterface $v = null) : ?DateTimeInterface
    {
        if(!is_null($v)) {
            $this->end = $v;
        }
        
        return $this->end;
    }
    
    public function withEnd(DateTimeInterface $v) : self
    {
        $this->end($v);
        return $this;
    }
    
    public function notes(string $v = null) : string
    {
        if(!is_null($v)) 
        {
            $this->notes = $v;
        }
        
        return $this->notes;
    }
    
    public function withNotes(string $v) : self
    {
        $this->notes($v);
        return $this;
    }
    
    public function badges(array $v = null) : array
    {
        if(!is_null($v)) 
        {
            $this->badges = $v;
        }
        
        return $this->badges;
    }
    
    public function addBadge(string $v) : self
    {
        return $this->addBadges($v);
    }
    
    public function addBadges(string ...$v) : self
    {
        foreach($v as $badge)
        {
            $this->badges[] = $badge;            
        }
        
        return $this;
    }
    
    public function removeBadge(string $v) : self
    {
        return $this->removeBadges($v);
    }
    
    public function removeBadges(string ...$v) : self
    {
        foreach($v as $badge)
        {
            $this->badges = array_filter($this->badges, function($b) use ($badge) {
                return $b != $badge;
            });
        }
        return $this;
    }
}
