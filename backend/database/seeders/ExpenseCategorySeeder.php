<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Food', 'icon' => 'pi pi-shop', 'color' => '#f59e0b'],
            ['name' => 'Transport', 'icon' => 'pi pi-car', 'color' => '#3b82f6'],
            ['name' => 'Hotel', 'icon' => 'pi pi-building', 'color' => '#8b5cf6'],
            ['name' => 'Shopping', 'icon' => 'pi pi-shopping-bag', 'color' => '#ec4899'],
            ['name' => 'Entertainment', 'icon' => 'pi pi-ticket', 'color' => '#10b981'],
            ['name' => 'Medical', 'icon' => 'pi pi-heart', 'color' => '#ef4444'],
            ['name' => 'Fuel', 'icon' => 'pi pi-bolt', 'color' => '#eab308'],
            ['name' => 'Miscellaneous', 'icon' => 'pi pi-box', 'color' => '#64748b'],
        ];

        foreach ($categories as $category) {
            ExpenseCategory::updateOrCreate(
                ['name' => $category['name']],
                ['icon' => $category['icon'], 'color' => $category['color']]
            );
        }
    }
}
