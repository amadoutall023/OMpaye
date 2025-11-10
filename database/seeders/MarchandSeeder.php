<?php
namespace Database\Seeders;

use App\Models\Marchand;
use Illuminate\Database\Seeder;

class MarchandSeeder extends Seeder
{
    public function run(): void
    {
        Marchand::create([
            'nom' => 'Orange Money',
            'code' => 'OMN001',
            'description' => 'Paiement via Orange Money.'
        ]);

        // Générer 10 marchands aléatoires
        Marchand::factory(10)->create();
    }
}

