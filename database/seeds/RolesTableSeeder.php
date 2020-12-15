<?php

use Illuminate\Database\Seeder;
use App\Roles;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Roles::query()->truncate();
        Roles::create([
            'user_id'=>1,
            'user_type'=>'A'
        ]);
        Roles::create([
            'user_id'=>2,
            'user_type'=>'A'
        ]);
        Roles::create([
            'user_id'=>1,
            'user_type'=>'T'
        ]);
        Roles::create([
            'user_id'=>3,
            'user_type'=>'T'
        ]);
        Roles::create([
            'user_id'=>4,
            'user_type'=>'T'
        ]);
        Roles::create([
            'user_id'=>5,
            'user_type'=>'T'
        ]);
        Roles::create([
            'user_id'=>6,
            'user_type'=>'T'
        ]);
        Roles::create([
            'user_id'=>7,
            'user_type'=>'S'
        ]);
        Roles::create([
            'user_id'=>8,
            'user_type'=>'S'
        ]);
        Roles::create([
            'user_id'=>9,
            'user_type'=>'S'
        ]);
        Roles::create([
            'user_id'=>10,
            'user_type'=>'S'
        ]);
        Roles::create([
            'user_id'=>11,
            'user_type'=>'S'
        ]);
        Roles::create([
            'user_id'=>12,
            'user_type'=>'S'
        ]);
        Roles::create([
            'user_id'=>13,
            'user_type'=>'S'
        ]);
        Roles::create([
            'user_id'=>14,
            'user_type'=>'S'
        ]);
        Roles::create([
            'user_id'=>15,
            'user_type'=>'S'
        ]);
        Roles::create([
            'user_id'=>16,
            'user_type'=>'S'
        ]);
        Roles::create([
            'user_id'=>17,
            'user_type'=>'A'
        ]);
        Roles::create([
            'user_id'=>18,
            'user_type'=>'A'
        ]);
        Roles::create([
            'user_id'=>19,
            'user_type'=>'T'
        ]);
        Roles::create([
            'user_id'=>20,
            'user_type'=>'T'
        ]);
        Roles::create([
            'user_id'=>21,
            'user_type'=>'T'
        ]);
        Roles::create([
            'user_id'=>22,
            'user_type'=>'T'
        ]);
        Roles::create([
            'user_id'=>23,
            'user_type'=>'T'
        ]);
    }
}
