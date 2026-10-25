<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    private const GROUPS = [
        'shop' => ['shop_name', 'shop_phone', 'shop_email', 'shop_address'],
        'interest' => ['default_rate', 'default_term', 'max_loan_percent'],
        'tax' => ['tax_enabled', 'tax_name', 'tax_rate'],
        'currency' => ['currency_symbol', 'currency_code', 'khr_rate'],
    ];

    public function index()
    {
        return view('admin.settings', ['s' => Setting::all_()]);
    }

    public function save(Request $r, string $group)
    {
        abort_unless(isset(self::GROUPS[$group]), 404);

        $rules = [
            'shop' => [
                'shop_name' => 'required|string|max:100',
                'shop_phone' => 'nullable|string|max:50',
                'shop_email' => 'nullable|email|max:100',
                'shop_address' => 'nullable|string|max:255',
            ],
            'interest' => [
                'default_rate' => 'required|numeric|min:0|max:100',
                'default_term' => 'required|integer|min:1|max:60',
                'max_loan_percent' => 'required|numeric|min:1|max:100',
            ],
            'tax' => [
                'tax_enabled' => 'nullable|boolean',
                'tax_name' => 'required|string|max:30',
                'tax_rate' => 'required|numeric|min:0|max:100',
            ],
            'currency' => [
                'currency_symbol' => 'required|string|max:5',
                'currency_code' => 'required|string|max:5',
                'khr_rate' => 'required|numeric|min:1',
            ],
        ][$group];

        $data = $r->validate($rules);
        if ($group === 'tax') {
            $data['tax_enabled'] = $r->boolean('tax_enabled') ? '1' : '0';
        }
        Setting::put($data);

        return back()->with('success', 'បានរក្សាទុករួចរាល់ / Saved.')->with('tab', $group);
    }

    /* ---------- Backup ---------- */
    public function backup()
    {
        $name = 'pawnshopdb ' . now()->format('Y-m-d') . '.sql';

        return response()->streamDownload(function () {
            $this->dump(function (string $s) {
                echo $s;
            });
        }, $name, ['Content-Type' => 'application/sql']);
    }

    /* ---------- Restore ---------- */
    public function restore(Request $r)
    {
        $r->validate([
            'sql_file' => 'required|file|max:51200',
            'confirm' => 'required|in:RESTORE',
        ]);

        $sql = file_get_contents($r->file('sql_file')->getRealPath());
        if (!str_starts_with(ltrim($sql), '-- Pawn Shop backup')) {
            return back()->withErrors(['sql_file' => 'Invalid backup file.'])->with('tab', 'backup');
        }

        try {
            // បម្រុងទុកទិន្នន័យបច្ចុប្បន្នស្វ័យប្រវត្តិ មុនជំនួស
            $dir = storage_path('app/backups');
            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }
            $safety = $dir . '/pre-restore-' . now()->format('Ymd-His') . '.sql';
            $fh = fopen($safety, 'w');
            $this->dump(fn(string $s) => fwrite($fh, $s));
            fclose($fh);

            DB::connection()->getPdo()->exec($sql);
            Cache::forget('pawn.settings');
        } catch (\Throwable $e) {
            return back()->withErrors(['sql_file' => 'Restore failed: ' . $e->getMessage()])->with('tab', 'backup');
        }

        return back()
            ->with('success', 'បានស្ដារទិន្នន័យរួចរាល់។ ឯកសារបម្រុងមុនស្ដារ៖ storage/app/backups')
            ->with('tab', 'backup');
    }

    /* ---------- SQL dump (ប្រើរួមគ្នា) ---------- */
    private function dump(callable $write): void
    {
        $pdo = DB::connection()->getPdo();
        $write('-- Pawn Shop backup ' . now() . "\nSET FOREIGN_KEY_CHECKS=0;\n\n");

        foreach (DB::select('SHOW FULL TABLES WHERE Table_type = "BASE TABLE"') as $row) {
            $table = array_values((array) $row)[0];
            $create = array_values((array) DB::select("SHOW CREATE TABLE `$table`")[0])[1];

            $write("DROP TABLE IF EXISTS `$table`;\n$create;\n\n");

            DB::table($table)->orderByRaw('1')->chunk(500, function ($rows) use ($table, $pdo, $write) {
                foreach ($rows as $r) {
                    $vals = array_map(fn($v) => $v === null ? 'NULL' : $pdo->quote((string) $v), (array) $r);
                    $write("INSERT INTO `$table` VALUES (" . implode(',', $vals) . ");\n");
                }
            });
            $write("\n");
        }
        $write("SET FOREIGN_KEY_CHECKS=1;\n");
    }
}