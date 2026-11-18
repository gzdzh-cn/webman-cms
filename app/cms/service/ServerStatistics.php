<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\Paths;
use app\cms\config\DeploymentConfig;

/** An on-demand, bounded, read-only snapshot. No caller-supplied paths or PIDs. */
final class ServerStatistics
{
    private const UNSUPPORTED = '当前平台不支持';
    private const UNAVAILABLE = '暂不可用';

    public function collect(array $runtimeRows): array
    {
        $linux = PHP_OS_FAMILY === 'Linux';
        $host = $linux ? $this->linuxHost() : $this->macHost();
        $site = $linux ? $this->linuxProcesses() : $this->macProcesses();
        $separate = DeploymentConfig::get('performance.separate_task_processes') === true;
        $mode = $separate ? '高性能模式（任务独立进程）' : '节省内存模式（任务合并进程）';
        $scope = '仅本站主进程及其直属工作进程；按需采样。';
        $pssNote = 'PSS 按共享进程数分摊共享页。' . ($site['complete']['pss'] ? $scope : '采样不完整，不能作为完整整站内存。');
        $memory = [
            $this->row('host_total', '服务器总内存', self::bytes($host['total']), 'Linux MemTotal；容器限制另列。'),
            $this->row('host_used', '服务器已用内存', self::bytes($host['used']), '总量 − 可用（MemAvailable）；包含对可回收内存的估计。'),
            $this->row('host_available', '服务器可用内存', self::bytes($host['available']), 'MemAvailable，区别于完全空闲内存。'),
            $this->row('host_usage', '服务器内存使用率', $host['usage'] === null ? self::unsupported() : $host['usage'] . '%', '（总量 − 可用）÷ 总量。'),
            $this->row('host_free', '服务器空闲内存', self::bytes($host['free'])),
            $this->row('host_cache', '服务器文件缓存', self::bytes($host['cache']), 'Linux Cached；可回收 slab 单独显示。'),
            $this->row('host_buffers', '服务器缓冲区', self::bytes($host['buffers'])),
            $this->row('host_reclaimable', '可回收 slab', self::bytes($host['reclaimable'])),
            $this->row('swap_total', 'Swap 总量', self::bytes($host['swap_total'])),
            $this->row('swap_used', 'Swap 已用', self::bytes($host['swap_used'])),
            $this->row('swap_free', 'Swap 可用', self::bytes($host['swap_free'])),
            $this->row('site_pss', '本站 PSS 合计', $this->total($site, 'pss'), $pssNote),
            $this->row('site_rss', '本站 RSS 合计', $this->total($site, 'rss'), '共享内存可能重复计入；' . ($site['complete']['rss'] ? $scope : '采样不完整。')),
            $this->row('site_private', '本站私有常驻内存', $this->total($site, 'private'), 'Private_Clean + Private_Dirty（含私有大页）；' . ($site['complete']['private'] ? $scope : '采样不完整或当前平台不支持。')),
            $this->row('site_processes', '本站实际进程数', ($site['verified'] ? (string)count($site['processes']) : self::UNAVAILABLE) . ($site['membership_complete'] ? '' : '（采样不完整）'), $scope),
            $this->row('task_mode', '任务进程模式', $mode, '本次进程生效配置；修改后完整重启。'),
        ];
        $disk = Paths::directory('site_root');
        $diskTotal = @disk_total_space($disk); $diskFree = @disk_free_space($disk);
        $resources = [
            $this->row('cpu_count', 'CPU 逻辑核数', $host['cpu'] === null ? self::UNAVAILABLE : (string)$host['cpu']),
            $this->row('load_average', '负载（1 / 5 / 15 分钟）', $host['load'] === null ? self::UNAVAILABLE : implode(' / ', array_map(static fn($n) => number_format($n, 2, '.', ''), $host['load']))),
            $this->row('server_uptime', '服务器运行时长', self::duration($host['uptime'])),
            $this->row('disk_total', '站点磁盘总量', self::bytes($diskTotal === false ? null : (int)$diskTotal), '站点所在文件系统，不遍历目录。'),
            $this->row('disk_available', '站点磁盘可用', self::bytes($diskFree === false ? null : (int)$diskFree)),
            $this->row('disk_usage', '站点磁盘使用率', $diskTotal > 0 && $diskFree !== false ? round(100 * (1 - $diskFree / $diskTotal), 2) . '%' : self::UNAVAILABLE),
            $this->row('container_limit', '容器内存限制', $host['container']['status'] === 'limited' ? self::bytes($host['container']['limit']) : $host['container']['note'], '独立于宿主机总内存；不以限制替代 MemTotal。'),
            $this->row('container_current', '容器当前内存', self::bytes($host['container']['current']), 'cgroup 内存口径，与本站 PSS 不同。'),
        ];
        $environment = array_merge($runtimeRows, [
            $this->row('task_mode', '任务进程模式', $mode),
            $this->row('memory_watchdog', '内存监护位置', $separate ? '独立 monitor 进程' : '主进程（每 60 秒）'),
        ]);
        $byKey = array_column(array_merge($memory, $environment), null, 'key');
        $summary = [];
        foreach (['host_total', 'host_available', 'host_usage', 'site_pss', 'site_processes', 'task_mode', 'php_memory_usage', 'php_memory_limit', 'os', 'php', 'database'] as $key) {
            if (isset($byKey[$key])) $summary[] = $byKey[$key];
        }
        return ['sampled_at'=>date(DATE_ATOM), 'summary'=>$summary,
            'groups'=>['memory'=>$memory, 'resources'=>$resources, 'environment'=>$environment],
            'host'=>$host, 'site'=>$site];
    }

    /** Strict parser shared by real reads and malformed/missing-value regression tests. */
    public static function kibFields(?string $raw): array
    {
        $values = [];
        if ($raw !== null && preg_match_all('/^([A-Za-z_]+):\s+([0-9]{1,15}) kB\s*$/m', $raw, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) if ((float)$match[2] <= PHP_INT_MAX / 1024) $values[$match[1]] = (int)$match[2] * 1024;
        }
        return $values;
    }

    public static function memoryValues(?string $raw): array
    {
        $v = self::kibFields($raw); $total = $v['MemTotal'] ?? null; $available = $v['MemAvailable'] ?? null;
        if ($total === null || $total <= 0 || $available === null || $available > $total) $used = $usage = null;
        else { $used = $total - $available; $usage = round(100 * $used / $total, 2); }
        $swapTotal = $v['SwapTotal'] ?? null; $swapFree = $v['SwapFree'] ?? null;
        return ['total'=>$total, 'available'=>$available, 'used'=>$used, 'usage'=>$usage,
            'free'=>$v['MemFree'] ?? null, 'cache'=>$v['Cached'] ?? null, 'buffers'=>$v['Buffers'] ?? null,
            'reclaimable'=>$v['SReclaimable'] ?? null, 'swap_total'=>$swapTotal, 'swap_free'=>$swapFree,
            'swap_used'=>$swapTotal !== null && $swapFree !== null && $swapFree <= $swapTotal ? $swapTotal - $swapFree : null];
    }

    private function linuxHost(): array
    {
        $host = self::memoryValues($this->read('/proc/meminfo'));
        $cpu = $this->read('/sys/devices/system/cpu/online', 4096); $count = 0;
        if ($cpu !== null && preg_match('/^\d+(?:-\d+)?(?:,\d+(?:-\d+)?)*\s*$/D', $cpu)) {
            foreach (explode(',', trim($cpu)) as $range) { $ends = explode('-', $range); $count += isset($ends[1]) ? (int)$ends[1] - (int)$ends[0] + 1 : 1; }
        }
        $uptime = $this->read('/proc/uptime', 256);
        return $host + ['cpu'=>$count > 0 ? $count : null, 'load'=>($load = sys_getloadavg()) === false ? null : $load,
            'uptime'=>$uptime !== null && preg_match('/^([0-9.]+) /', $uptime, $m) ? (int)(float)$m[1] : null,
            'container'=>$this->container()];
    }

    private function macHost(): array
    {
        $host = self::memoryValues(null);
        if (PHP_OS_FAMILY === 'Darwin') {
            $total = trim($this->command(['/usr/sbin/sysctl', '-n', 'hw.memsize']) ?? '');
            $cpu = trim($this->command(['/usr/sbin/sysctl', '-n', 'hw.logicalcpu']) ?? '');
            $boot = $this->command(['/usr/sbin/sysctl', '-n', 'kern.boottime']) ?? '';
            $host['total'] = ctype_digit($total) ? (int)$total : null;
        }
        return $host + ['cpu'=>isset($cpu) && ctype_digit($cpu) ? (int)$cpu : null,
            'load'=>($load = sys_getloadavg()) === false ? null : $load,
            'uptime'=>isset($boot) && preg_match('/sec = (\d+)/', $boot, $m) ? max(0, time() - (int)$m[1]) : null,
            'container'=>['status'=>'unsupported', 'limit'=>null, 'current'=>null, 'note'=>self::UNSUPPORTED]];
    }

    private function linuxProcesses(): array
    {
        $empty = $this->emptyProcesses();
        $master = $this->masterPid(); $me = getmypid();
        if (!$master || !$me) return $empty;
        $masterIdentity = $this->identity($master); $selfIdentity = $this->identity($me);
        if (!$masterIdentity || !$selfIdentity || $selfIdentity['parent'] !== $master || !$this->linuxMaster($master)) return $empty;
        $before = $this->children($master);
        if ($before === null || !in_array($me, $before, true)) return $empty;
        $processes = []; $complete = true;
        $uptime = $this->read('/proc/uptime', 256); $seconds = $uptime === null ? null : (float)explode(' ', $uptime)[0];
        $ticks = $this->clockTicks();
        foreach (array_merge([$master], $before) as $pid) {
            $identity = $this->identity($pid);
            if (!$identity || ($pid !== $master && $identity['parent'] !== $master)) { $complete = false; continue; }
            $status = self::kibFields($this->read('/proc/' . $pid . '/status'));
            $smaps = self::kibFields($this->read('/proc/' . $pid . '/smaps_rollup'));
            if ($identity !== $this->identity($pid)) { $complete = false; continue; }
            $processes[] = ['pid'=>$pid, 'category'=>$pid === $master ? '主进程' : ($pid === $me ? '当前 HTTP 进程' : '其他本站子进程'),
                'pss'=>$smaps['Pss'] ?? null, 'rss'=>$smaps['Rss'] ?? $status['VmRSS'] ?? null,
                'private'=>isset($smaps['Private_Clean'], $smaps['Private_Dirty']) ? $smaps['Private_Clean'] + $smaps['Private_Dirty'] + ($smaps['Private_Hugetlb'] ?? 0) : null,
                'uptime'=>$seconds !== null && $ticks !== null ? max(0, (int)($seconds - $identity['start'] / $ticks)) : null];
        }
        $complete = $complete && $before === $this->children($master) && $master === $this->masterPid()
            && $masterIdentity === $this->identity($master) && $selfIdentity === $this->identity($me);
        // A changed master invalidates ownership of the entire snapshot, rather than publishing another instance's data.
        if ($master !== $this->masterPid() || $masterIdentity !== $this->identity($master)) return $empty;
        return $this->aggregate($processes, $complete);
    }

    private function linuxMaster(int $pid): bool
    {
        $raw = $this->read('/proc/' . $pid . '/cmdline'); if ($raw === null) return false;
        $command = trim(explode("\0", $raw)[0]);
        if ($this->masterCommand($command)) return true;
        // micro runtimes may preserve argv. Verify executable + working directory + exact arguments.
        $binary = \Phar::running(false); $args = explode("\0", $raw);
        $cwd = @readlink('/proc/' . $pid . '/cwd');
        $entry = $args[0] ?? '';
        if ($entry !== '' && !str_starts_with($entry, '/') && is_string($cwd)) $entry = $cwd . '/' . $entry;
        // Under CPU emulation /proc/exe can identify a different runtime before
        // and after fork. Exact binary argv + own root are checked together with
        // PID-file, parent and start-time identities in linuxProcesses().
        return $binary !== '' && $entry !== '' && realpath($binary) === realpath($entry)
            && realpath(Paths::directory('site_root')) === $cwd
            && in_array($args[1] ?? '', ['start', 'restart'], true);
    }

    private function macProcesses(): array
    {
        $empty = $this->emptyProcesses(); if (PHP_OS_FAMILY !== 'Darwin') return $empty;
        $master = $this->masterPid(); if (!$master) return $empty;
        $command = trim($this->command(['/bin/ps', '-p', (string)$master, '-o', 'command=']) ?? '');
        if (!$this->masterCommand($command)) return $empty;
        $before = $this->macTable();
        if (!isset($before[getmypid()], $before[$master]) || $before[getmypid()]['parent'] !== $master) return $empty;
        $owned = array_filter($before, static fn($p, $pid) => $pid === $master || $p['parent'] === $master, ARRAY_FILTER_USE_BOTH);
        if (count($owned) > 128) return $empty;
        $after = $this->macTable(); $processes = []; $complete = true;
        foreach ($owned as $pid => $process) {
            if (!isset($after[$pid]) || $after[$pid]['start'] !== $process['start'] || $after[$pid]['parent'] !== $process['parent']) { $complete = false; continue; }
            $processes[] = ['pid'=>$pid, 'category'=>$pid === $master ? '主进程' : ($pid === getmypid() ? '当前 HTTP 进程' : '其他本站子进程'),
                'pss'=>null, 'rss'=>$process['rss'], 'private'=>null, 'uptime'=>$process['uptime']];
        }
        $afterOwned = array_filter($after, static fn($p, $pid) => $pid === $master || $p['parent'] === $master, ARRAY_FILTER_USE_BOTH);
        if ($master !== $this->masterPid() || ($before[$master]['start'] ?? null) !== ($after[$master]['start'] ?? null)) return $empty;
        return $this->aggregate($processes, $complete && array_keys($owned) === array_keys($afterOwned));
    }

    private function macTable(): array
    {
        $raw = $this->command(['/bin/ps', '-axo', 'pid=,ppid=,rss=,etime=,lstart=']); $table = [];
        foreach (explode("\n", $raw ?? '') as $line) {
            if (!preg_match('/^\s*(\d+)\s+(\d+)\s+(\d+)\s+([\d:-]+)\s+(.+?)\s*$/D', $line, $m)) continue;
            $time = explode('-', $m[4]); $days = count($time) === 2 ? (int)array_shift($time) : 0;
            $parts = array_reverse(explode(':', $time[0]));
            $table[(int)$m[1]] = ['parent'=>(int)$m[2], 'rss'=>(int)$m[3] * 1024, 'start'=>$m[5],
                'uptime'=>$days * 86400 + (int)$parts[0] + (int)($parts[1] ?? 0) * 60 + (int)($parts[2] ?? 0) * 3600];
        }
        ksort($table); return $table;
    }

    private function masterPid(): ?int
    {
        $raw = trim($this->read(Paths::runtime('webman.pid'), 32) ?? '');
        return ctype_digit($raw) && (int)$raw > 1 ? (int)$raw : null;
    }

    private function masterCommand(string $command): bool
    {
        $entries = [Paths::code('webman'), Paths::code('start.php')];
        // Native micro builds can set a Workerman title with the external ELF
        // entry, while code paths still point into the PHAR.
        if ($binary = \Phar::running(false)) $entries[] = $binary;
        foreach ($entries as $entry) if ($command === 'WorkerMan: master process  start_file=' . $entry) return true;
        return false;
    }

    private function identity(int $pid): ?array
    {
        $raw = $this->read('/proc/' . $pid . '/stat', 4096);
        if ($raw === null || !preg_match('/^' . $pid . ' \(.*\) ([A-Z]) (.*)$/sD', trim($raw), $m) || in_array($m[1], ['Z','X'], true)) return null;
        $fields = preg_split('/\s+/', $m[2]);
        return isset($fields[18]) && ctype_digit($fields[0]) && ctype_digit($fields[18]) ? ['parent'=>(int)$fields[0], 'start'=>(int)$fields[18]] : null;
    }

    private function children(int $master): ?array
    {
        $raw = $this->read('/proc/' . $master . '/task/' . $master . '/children', 4096);
        if ($raw === null || !preg_match('/^\s*(?:\d+\s*)*$/D', $raw)) return null;
        $pids = array_values(array_unique(array_map('intval', preg_split('/\s+/', trim($raw), -1, PREG_SPLIT_NO_EMPTY))));
        if (count($pids) > 128) return null;
        sort($pids); return $pids;
    }

    private function clockTicks(): ?int
    {
        if (function_exists('posix_sysconf') && defined('POSIX_SC_CLK_TCK')) {
            $ticks = posix_sysconf(POSIX_SC_CLK_TCK);
            if ($ticks > 0) return $ticks;
        }
        $raw = $this->read('/proc/self/auxv', 4096); if ($raw === null) return null;
        $format = PHP_INT_SIZE === 8 ? (pack('L', 1) === "\x01\0\0\0" ? 'P*' : 'J*') : 'L*';
        $values = array_values(unpack($format, $raw) ?: []);
        for ($i = 0; $i + 1 < count($values); $i += 2) if ($values[$i] === 17 && $values[$i + 1] > 0) return (int)$values[$i + 1];
        return null;
    }

    /** Resolve cgroup membership against its mount; inspect parent limits too. No paths are returned. */
    private function container(): array
    {
        $unknown = ['status'=>'unavailable', 'limit'=>null, 'current'=>null, 'note'=>'未识别到可读取的容器限制'];
        $membership = $this->read('/proc/self/cgroup'); $mounts = $this->read('/proc/self/mountinfo');
        if ($membership === null || $mounts === null) return $unknown;
        foreach (explode("\n", $membership) as $line) {
            $parts = explode(':', $line, 3); if (count($parts) !== 3) continue;
            $v2 = $parts[1] === ''; if (!$v2 && !in_array('memory', explode(',', $parts[1]), true)) continue;
            foreach (explode("\n", $mounts) as $mount) {
                $halves = explode(' - ', $mount, 2); if (count($halves) !== 2) continue;
                $left = explode(' ', $halves[0]); $right = explode(' ', $halves[1]);
                if (($right[0] ?? '') !== ($v2 ? 'cgroup2' : 'cgroup') || (!$v2 && !in_array('memory', explode(',', $right[2] ?? ''), true))) continue;
                $root = $left[3] ?? ''; $point = $left[4] ?? ''; $member = $parts[2];
                if (!$this->safeSystemPath($root) || !$this->safeSystemPath($point) || !$this->safeSystemPath($member)) continue;
                if ($root !== '/' && $member !== $root && !str_starts_with($member, $root . '/')) continue;
                $directory = rtrim($point, '/') . '/' . ltrim($root === '/' ? $member : substr($member, strlen($root)), '/');
                $directory = rtrim($directory, '/'); $limit = null; $current = null; $readable = false;
                for ($depth = 0; $depth < 32; $depth++) {
                    $raw = trim($this->read($directory . ($v2 ? '/memory.max' : '/memory.limit_in_bytes'), 128) ?? '');
                    if ($raw !== '') $readable = true;
                    if (ctype_digit($raw) && (float)$raw < 9.0e18) {
                        $candidate = (int)$raw;
                        if ($limit === null || $candidate < $limit) {
                            $limit = $candidate;
                            $usage = trim($this->read($directory . ($v2 ? '/memory.current' : '/memory.usage_in_bytes'), 128) ?? '');
                            $current = ctype_digit($usage) ? (int)$usage : null;
                        }
                    }
                    if ($directory === $point) break;
                    $parent = dirname($directory); if ($parent === $directory || ($parent !== $point && !str_starts_with($parent, $point . '/'))) break;
                    $directory = $parent;
                }
                if ($readable) return ['status'=>$limit === null ? 'unlimited' : 'limited', 'limit'=>$limit, 'current'=>$current, 'note'=>$limit === null ? '未设置有限内存上限' : '已识别 cgroup 限制'];
            }
        }
        return $unknown;
    }

    private function safeSystemPath(string $path): bool
    {
        return str_starts_with($path, '/') && !preg_match('#(?:^|/)\.{1,2}(?:/|$)|[\\\\\x00-\x20\x7f]#', $path);
    }

    private function emptyProcesses(): array { return $this->aggregate([], false); }

    private function aggregate(array $processes, bool $membershipComplete): array
    {
        $totals = []; $complete = []; $counts = [];
        foreach (['pss', 'rss', 'private'] as $key) {
            $values = array_values(array_filter(array_column($processes, $key), static fn($v) => $v !== null));
            $totals[$key] = $values ? array_sum($values) : null;
            $counts[$key] = count($values);
            $complete[$key] = $membershipComplete && count($processes) > 0 && count($values) === count($processes);
        }
        return ['verified'=>count($processes) > 0, 'membership_complete'=>$membershipComplete, 'complete'=>$complete, 'sampled_counts'=>$counts, 'totals'=>$totals, 'processes'=>$processes];
    }

    private function total(array $site, string $key): string
    {
        $value = $site['totals'][$key];
        if ($value === null) return PHP_OS_FAMILY === 'Darwin' && $key !== 'rss' ? self::UNSUPPORTED : self::UNAVAILABLE . '（采样不完整）';
        return self::bytes($value) . ($site['complete'][$key] ? '' : '（部分采样 ' . $site['sampled_counts'][$key] . '/' . count($site['processes']) . '）');
    }

    private function read(string $path, int $max = 65536): ?string
    {
        $stream = @fopen($path, 'rb'); if (!$stream) return null;
        try { $raw = @stream_get_contents($stream, $max + 1); return is_string($raw) && strlen($raw) <= $max ? $raw : null; }
        finally { fclose($stream); }
    }

    /** Fixed macOS read-only programs only; total deadline and output cap. */
    private function command(array $args): ?string
    {
        if (!function_exists('proc_open')) return null;
        $proc = @proc_open($args, [0=>['file','/dev/null','r'],1=>['pipe','w'],2=>['file','/dev/null','w']], $pipes, null, ['LC_ALL'=>'C']);
        if (!is_resource($proc)) return null;
        stream_set_blocking($pipes[1], false); $output = ''; $deadline = microtime(true) + 0.8; $exit = -1;
        try {
            do {
                $output .= (string)stream_get_contents($pipes[1], 1048577 - strlen($output));
                $state = proc_get_status($proc);
                if (!$state['running']) { $exit = $state['exitcode']; break; }
                if (strlen($output) > 1048576 || microtime(true) > $deadline) { proc_terminate($proc); return null; }
                usleep(5000);
            } while (true);
            $output .= (string)stream_get_contents($pipes[1], 1048577 - strlen($output));
            return $exit === 0 && strlen($output) <= 1048576 ? $output : null;
        } finally { fclose($pipes[1]); proc_close($proc); }
    }

    public static function bytes(?int $bytes): string
    {
        if ($bytes === null) return self::unsupported();
        $value = (float)$bytes;
        foreach (['B','KiB','MiB','GiB','TiB'] as $unit) { if ($value < 1024 || $unit === 'TiB') break; $value /= 1024; }
        return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.') . ' ' . $unit;
    }

    public static function duration(?int $seconds): string
    {
        if ($seconds === null) return self::UNAVAILABLE;
        return intdiv($seconds, 86400) . ' 天 ' . intdiv($seconds % 86400, 3600) . ' 时 ' . intdiv($seconds % 3600, 60) . ' 分';
    }

    private static function unsupported(): string { return PHP_OS_FAMILY === 'Linux' ? self::UNAVAILABLE : self::UNSUPPORTED; }
    private function row(string $key, string $label, string $value, string $note = ''): array { return compact('key', 'label', 'value', 'note'); }
}
