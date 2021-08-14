<?php

use Illuminate\Database\Seeder;
use App\Organization;

class OrganizationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Organization::query()->truncate();
        Organization::create([
            'id'=>'G',
            'name'=>'Geoquimica',
            'faculty_id'=>'CIENS',
            'website'=>'http://www.ciens.ucv.ve/ciens/geoquimica/'
        ]);
        /*Organization::create([
            'id'=>'C',
            'name'=>'Computacion',
            'faculty_id'=>'CIENS',
            'website'=>'http://www.ciens.ucv.ve/ciens/computacion/'
        ]);*/

        Organization::create([
            'id'=>'ICT',
            'name'=>'Instituto de Ciencias de la Tierra',
            'faculty_id'=>'CIENS',
            'organization_id'=>'G',
            'website'=>'http://gestiongeo.ciens.ucv.ve/'
        ]);
       /* Organization::create([
            'id'=>'B',
            'name'=>'Biologia',
            'faculty_id'=>'CIENS',
            'organization_id'=>'B',
            'website'=>'http://www.ciens.ucv.ve/biologia/'
        ]);*/
    }
}
