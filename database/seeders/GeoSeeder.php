<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Geo;

class GeoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ruta del archivo CSV
        $csvFile = storage_path('app\csv\Ut6OGbrwKZ.csv');
        
        // Leer el archivo CSV
        $file = fopen($csvFile, 'r');

            //Vamos recorriendo y generando nuevos Geo        
            while (($data = fgetcsv($file)) !== false) {

                Geo::create([
                    'id' => $data[0],
                    'description' => utf8_encode($data[1]),
                    'type_geo_id' => $data[2],
                    'geo_id' => $data[3] === 'null' ? null : $data[3],
                ]);

            }
        fclose($file);
    }
}
