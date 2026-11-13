<?php
declare(strict_types=1);
namespace app\cms\support;

/** Numeric, five-field Cron. No commands, macros, seconds or executable input. */
final class CronExpression
{
    private array $fields = [];
    private array $wildcards = [];
    public readonly string $expression;

    public function __construct(string $expression)
    {
        if (strlen($expression) > 200) throw new \InvalidArgumentException('Cron表达式过长');
        $parts = preg_split('/\s+/', trim($expression));
        if (count($parts) !== 5) throw new \InvalidArgumentException('Cron须为五段：分 时 日 月 星期');
        $this->expression = implode(' ', $parts);
        foreach ([[0,59],[0,23],[1,31],[1,12],[0,7]] as $i => [$min,$max]) {
            $values = [];
            $this->wildcards[$i] = str_starts_with($parts[$i], '*');
            foreach (explode(',', $parts[$i]) as $part) {
                if (!preg_match('/^(\*|\d{1,2}(?:-\d{1,2})?)(?:\/(\d{1,2}))?$/D', $part, $m)) throw new \InvalidArgumentException('Cron仅支持数字、*、逗号、范围及步长');
                $step = isset($m[2]) ? (int)$m[2] : 1;
                if ($step < 1 || $step > $max-$min+1) throw new \InvalidArgumentException('Cron步长超出范围');
                if ($m[1] === '*') [$start,$end] = [$min,$max];
                elseif (str_contains($m[1], '-')) [$start,$end] = array_map('intval', explode('-', $m[1]));
                else { $start = (int)$m[1]; $end = isset($m[2]) ? $max : $start; }
                if ($start < $min || $end > $max || $start > $end) throw new \InvalidArgumentException('Cron数值或范围无效');
                for ($n=$start; $n<=$end; $n+=$step) $values[$i===4 && $n===7 ? 0 : $n] = true;
            }
            ksort($values); $this->fields[$i] = $values;
        }
    }

    public function matches(\DateTimeImmutable $date): bool
    {
        return isset($this->fields[0][(int)$date->format('i')], $this->fields[1][(int)$date->format('G')]) && $this->matchesDay($date);
    }

    private function matchesDay(\DateTimeImmutable $date): bool
    {
        if (!isset($this->fields[3][(int)$date->format('n')])) return false;
        $day = isset($this->fields[2][(int)$date->format('j')]);
        $week = isset($this->fields[4][(int)$date->format('w')]);
        return $this->wildcards[2] || $this->wildcards[4] ? $day && $week : $day || $week;
    }

    /** Find strictly after the supplied instant, including leap days. */
    public function next(\DateTimeImmutable $after): \DateTimeImmutable
    {
        $day = $after->setTime(0,0);
        for ($d=0; $d<366*8; $d++, $day=$day->modify('+1 day')->setTime(0,0)) {
            if (!$this->matchesDay($day)) continue;
            $end=$day->modify('+1 day')->setTime(0,0)->getTimestamp();
            $start=max($day->getTimestamp(),intdiv($after->getTimestamp(),60)*60+60);
            // Walk real minutes on eligible days so DST folds and gaps stay correct.
            for ($stamp=$start; $stamp<$end; $stamp+=60) {
                $candidate=(new \DateTimeImmutable('@'.$stamp))->setTimezone($after->getTimezone());
                if ($this->matches($candidate)) return $candidate;
            }
        }
        throw new \InvalidArgumentException('Cron在未来八年内没有有效执行时间，请检查日期');
    }
}
