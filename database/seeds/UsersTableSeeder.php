<?php

use Illuminate\Database\Seeder;
use App\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        User::query()->truncate();
        User::create([
            'identification' => '16922508',
            'first_name' => 'Laura',
            'first_surname' => 'Torres',
            'mobile' => '(1234) 567-8901',
            'email' => 'laura.torres@ciens.ucv.ve',
            'password' => Hash::make('16922508'),
            'level_instruction'=>'Dr',
            'active'=>true,
            'sex' => 'F',
            'nationality'=>'V',
            'organization_id'=>'ICT'
        ]);
        /*User::create([
           'identification' => '24698917',
           'first_name' => 'Jose',
           'first_surname' => 'Ortiz',
           'mobile' => '(1234) 567-8901',
           'email' => 'jose@admin.com',
           'password' => Hash::make('24698917'),
           'level_instruction'=>'Dr',
           'active'=>true,
           'sex' => 'M',
           'nationality'=>'V',
           'organization_id'=>'ICT'
       ]);
       User::create([
           'identification' => '24698918',
           'first_name' => 'Felix',
           'first_surname' => 'Urbano',
           'mobile' => '(1234) 567-8901',
           'email' => 'felix@teacher.com',
           'password' => Hash::make('24698918'),
           'level_instruction'=>'Dr',
           'sex' => 'M',
           'active'=>true,
           'nationality'=>'V',
           'organization_id'=>'ICT',
       ]);
       User::create([
           'identification' => '24698919',
           'first_name' => 'Jonatan',
           'first_surname' => 'Castillo',
           'mobile' => '(1234) 567-8901',
           'email' => 'jonatan@teacher.com',
           'password' => Hash::make('24698919'),
           'level_instruction'=>'Dr',
           'sex' => 'M',
           'active'=>true,
           'nationality'=>'V',
           'organization_id'=>'ICT',
       ]);
       User::create([
           'identification' => '24698920',
           'first_name' => 'Jean',
           'first_surname' => 'Trujillo',
           'mobile' => '(1234) 567-8901',
           'email' => 'jean@teacher.com',
           'password' => Hash::make('24698920'),
           'level_instruction'=>'Dr',
           'sex' => 'M',
           'active'=>true,
           'nationality'=>'V',
           'organization_id'=>'ICT',
       ]);
       User::create([
           'identification' => '24698921',
           'first_name' => 'Jean',
           'first_surname' => 'Parada',
           'mobile' => '(1234) 567-8901',
           'email' => 'jeanp@teacher.com',
           'password' => Hash::make('24698921'),
           'level_instruction'=>'Dr',
           'sex' => 'M',
           'active'=>true,
           'nationality'=>'V',
           'organization_id'=>'ICT',
       ]);
       User::create([
           'identification' => '24698922',
           'first_name' => 'Thalia',
           'first_surname' => 'Rivas',
           'mobile' => '(1234) 567-8901',
           'email' => 'thalia@student.com',
           'password' => Hash::make('24698922'),
           'level_instruction'=>'Esp',
           'sex' => 'F',
           'active'=>true,
           'nationality'=>'V',
           'organization_id'=>'ICT',
       ]);
       User::create([
           'identification' => '24698923',
           'first_name' => 'Adrian',
           'first_surname' => 'Suarez',
           'mobile' => '(1234) 567-8901',
           'email' => 'adrian@student.com',
           'password' => Hash::make('24698923'),
           'level_instruction'=>'Lic',
           'sex' => 'M',
           'active'=>true,
           'nationality'=>'V',
           'organization_id'=>'ICT',
       ]);
       User::create([
           'identification' => '24698924',
           'first_name' => 'Maria',
           'first_surname' => 'Quintero',
           'mobile' => '(1234) 567-8901',
           'email' => 'maria@student.com',
           'password' => Hash::make('24698924'),
           'level_instruction'=>'Lic',
           'sex' => 'F',
           'active'=>true,
           'nationality'=>'V',
           'organization_id'=>'ICT',
       ]);
       User::create([
           'identification' => '24698925',
           'first_name' => 'Yenny',
           'first_surname' => 'Briceno',
           'mobile' => '(1234) 567-8901',
           'email' => 'yenny@student.com',
           'password' => Hash::make('24698925'),
           'level_instruction'=>'Lic',
           'sex' => 'F',
           'active'=>true,
           'nationality'=>'V',
           'organization_id'=>'ICT',
       ]);
       User::create([
           'identification' => '24698926',
           'first_name' => 'Erika',
           'first_surname' => 'Rosales',
           'mobile' => '(1234) 567-8901',
           'email' => 'erika@student.com',
           'password' => Hash::make('24698926'),
           'level_instruction'=>'Lic',
           'sex' => 'F',
           'active'=>true,
           'nationality'=>'V',
           'organization_id'=>'ICT',
       ]);
       User::create([
           'identification' => '24698927',
           'first_name' => 'Carolina',
           'first_surname' => 'Navera',
           'mobile' => '(1234) 567-8901',
           'email' => 'Carolina@student.com',
           'password' => Hash::make('24698927'),
           'level_instruction'=>'Lic',
           'sex' => 'F',
           'active'=>true,
           'nationality'=>'V',
           'organization_id'=>'ICT',
       ]);
       User::create([
           'identification' => '24698928',
           'first_name' => 'Mayra',
           'first_surname' => 'Perez',
           'mobile' => '(1234) 567-8901',
           'email' => 'mayra@student.com',
           'password' => Hash::make('24698928'),
           'level_instruction'=>'Lic',
           'sex' => 'F',
           'active'=>true,
           'nationality'=>'V',
           'organization_id'=>'ICT',
       ]);
       User::create([
           'identification' => '24698929',
           'first_name' => 'Macgiver',
           'first_surname' => 'Bracho',
           'mobile' => '(1234) 567-8901',
           'email' => 'macgiver@student.com',
           'password' => Hash::make('24698929'),
           'level_instruction'=>'Lic',
           'sex' => 'M',
           'active'=>true,
           'nationality'=>'V',
           'organization_id'=>'ICT',
       ]);
       User::create([
           'identification' => '24698930',
           'first_name' => 'Luzmar',
           'first_surname' => 'Caicebo',
           'mobile' => '(1234) 567-8901',
           'email' => 'luzmar@student.com',
           'password' => Hash::make('24698930'),
           'level_instruction'=>'Lic',
           'sex' => 'F',
           'active'=>true,
           'nationality'=>'V',
           'organization_id'=>'ICT',
       ]);
       User::create([
           'identification' => '24698931',
           'first_name' => 'Luis',
           'first_surname' => 'Ramos',
           'mobile' => '(1234) 567-8901',
           'email' => 'luis@student.com',
           'password' => Hash::make('24698931'),
           'level_instruction'=>'Lic',
           'sex' => 'M',
           'active'=>true,
           'nationality'=>'V',
           'organization_id'=>'ICT',
       ]);
       User::create([
           'identification' => '24698916',
           'first_name' => 'Hector',
           'first_surname' => 'Alayon',
           'mobile' => '(1234) 567-8901',
           'email' => 'hector@admin.com',
           'password' => Hash::make('24698916'),
           'level_instruction'=>'Dr',
           'active'=>true,
           'sex' => 'M',
           'nationality'=>'V',
           'organization_id'=>'C'
       ]);
       User::create([
           'identification' => '24698917',
           'first_name' => 'Jose',
           'first_surname' => 'Ortiz',
           'mobile' => '(1234) 567-8901',
           'email' => 'jose@admin.com',
           'password' => Hash::make('24698916'),
           'level_instruction'=>'Dr',
           'active'=>true,
           'sex' => 'M',
           'nationality'=>'V',
           'organization_id'=>'C'
       ]);
       User::create([
           'identification' => '24698932',
           'first_name' => 'Ilich',
           'first_surname' => 'Rondon',
           'mobile' => '(1234) 567-8901',
           'email' => 'ilich@teacher.com',
           'password' => Hash::make('24698932'),
           'level_instruction'=>'Dr',
           'sex' => 'M',
           'active'=>true,
           'nationality'=>'V',
           'organization_id'=>'C',
       ]);
       User::create([
           'identification' => '24698933',
           'first_name' => 'Glory',
           'first_surname' => 'Mendez',
           'mobile' => '(1234) 567-8901',
           'email' => 'glory@teacher.com',
           'password' => Hash::make('24698933'),
           'level_instruction'=>'Dr',
           'sex' => 'F',
           'active'=>true,
           'nationality'=>'V',
           'organization_id'=>'C',
       ]);
       User::create([
           'identification' => '24698934',
           'first_name' => 'Robert',
           'first_surname' => 'Arrieche',
           'mobile' => '(1234) 567-8901',
           'email' => 'robert@teacher.com',
           'password' => Hash::make('24698934'),
           'level_instruction'=>'Dr',
           'sex' => 'M',
           'active'=>true,
           'nationality'=>'V',
           'organization_id'=>'C',
       ]);
       User::create([
           'identification' => '24698935',
           'first_name' => 'Willman',
           'first_surname' => 'Delgado',
           'mobile' => '(1234) 567-8901',
           'email' => 'willman@teacher.com',
           'password' => Hash::make('24698935'),
           'level_instruction'=>'Dr',
           'sex' => 'M',
           'active'=>true,
           'nationality'=>'V',
           'organization_id'=>'C',
       ]);
       User::create([
           'identification' => '24698936',
           'first_name' => 'Steven',
           'first_surname' => 'Barreto',
           'mobile' => '(1234) 567-8901',
           'email' => 'steven@teacher.com',
           'password' => Hash::make('24698936'),
           'level_instruction'=>'Dr',
           'sex' => 'M',
           'active'=>true,
           'nationality'=>'V',
           'organization_id'=>'C',
       ]);
      User::create([
           'identification' => '24698937',
           'first_name' => 'Samuel',
           'first_surname' => 'Bolivar',
           'mobile' => '(1234) 567-8901',
           'email' => 'samuel@student.com',
           'password' => Hash::make('24698937'),
           'user_type' => 'S',  //S T A
           'level_instruction'=>'Lic',
           'sex' => 'M',
           'active'=>true,
           'nationality'=>'V',
           'organization_id'=>'C',
       ]);
       User::create([
           'identification' => '24698938',
           'first_name' => 'Roberto',
           'first_surname' => 'Bartolome',
           'mobile' => '(1234) 567-8901',
           'email' => 'roberto@student.com',
           'password' => Hash::make('24698938'),
           'user_type' => 'S',  //S T A
           'level_instruction'=>'Lic',
           'sex' => 'M',
           'active'=>true,
           'nationality'=>'V',
           'organization_id'=>'C',
       ]);
       User::create([
           'identification' => '24698939',
           'first_name' => 'Plutarco',
           'first_surname' => 'Guerrero',
           'mobile' => '(1234) 567-8901',
           'email' => 'mara@student.com',
           'password' => Hash::make('24698939'),
           'user_type' => 'S',  //S T A
           'level_instruction'=>'Lic',
           'sex' => 'M',
           'active'=>true,
           'nationality'=>'V',
           'organization_id'=>'C',
       ]);
       User::create([
           'identification' => '24698940',
           'first_name' => 'Pedro',
           'first_surname' => 'Quijada',
           'mobile' => '(1234) 567-8901',
           'email' => 'pedro@student.com',
           'password' => Hash::make('24698940'),
           'user_type' => 'S',  //S T A
           'level_instruction'=>'Lic',
           'sex' => 'M',
           'active'=>true,
           'nationality'=>'V',
           'organization_id'=>'C',
       ]);
       User::create([
           'identification' => '24698941',
           'first_name' => 'Nairoby',
           'first_surname' => 'Hurtado',
           'mobile' => '(1234) 567-8901',
           'email' => 'nairoby@student.com',
           'password' => Hash::make('24698941'),
           'user_type' => 'S',  //S T A
           'level_instruction'=>'Lic',
           'sex' => 'F',
           'active'=>true,
           'nationality'=>'V',
           'organization_id'=>'C',
       ]);
       User::create([
           'identification' => '24698942',
           'first_name' => 'Mildred',
           'first_surname' => 'Noguera',
           'mobile' => '(1234) 567-8901',
           'email' => 'mildred@student.com',
           'password' => Hash::make('24698942'),
           'user_type' => 'S',  //S T A
           'level_instruction'=>'Lic',
           'sex' => 'F',
           'active'=>true,
           'nationality'=>'V',
           'organization_id'=>'C',
       ]);
       User::create([
           'identification' => '24698943',
           'first_name' => 'Luis',
           'first_surname' => 'Alvarez',
           'mobile' => '(1234) 567-8901',
           'email' => 'luisA@student.com',
           'password' => Hash::make('24698943'),
           'user_type' => 'S',  //S T A
           'level_instruction'=>'Lic',
           'sex' => 'M',
           'active'=>true,
           'nationality'=>'V',
           'organization_id'=>'C',
       ]);
       User::create([
           'identification' => '24698944',
           'first_name' => 'Lisbeth',
           'first_surname' => 'Ramos',
           'mobile' => '(1234) 567-8901',
           'email' => 'lisbeth@student.com',
           'password' => Hash::make('24698944'),
           'user_type' => 'S',  //S T A
           'level_instruction'=>'Lic',
           'sex' => 'F',
           'active'=>true,
           'nationality'=>'V',
           'organization_id'=>'C',
       ]);
       User::create([
           'identification' => '24698945',
           'first_name' => 'Kenny',
           'first_surname' => 'Jimenez',
           'mobile' => '(1234) 567-8901',
           'email' => 'kenny@student.com',
           'password' => Hash::make('24698945'),
           'user_type' => 'S',  //S T A
           'level_instruction'=>'Lic',
           'sex' => 'M',
           'active'=>true,
           'nationality'=>'V',
           'organization_id'=>'C',
       ]);
       User::create([
           'identification' => '24698946',
           'first_name' => 'Katherine',
           'first_surname' => 'Palacios',
           'mobile' => '(1234) 567-8901',
           'email' => 'katherine@student.com',
           'password' => Hash::make('24698946'),
           'user_type' => 'S',
           'level_instruction'=>'Lic',
           'sex' => 'F',
           'active'=>true,
           'nationality'=>'V',
           'organization_id'=>'C',
       ]);
       User::create([
           'identification' => '24698947',
           'first_name' => 'Danny',
           'first_surname' => 'Caldeira',
           'mobile' => '(1234) 567-8901',
           'email' => 'danny@admin.com',
           'password' => Hash::make('24698947'),
           'user_type' => 'A',
           'level_instruction'=>'Dr',
           'sex' => 'M',
           'active'=>true,
           'nationality'=>'V',
           'organization_id'=>'B',
       ]);
       User::create([
           'identification' => '24698948',
           'first_name' => 'Marialis',
           'first_surname' => 'Bartoli',
           'mobile' => '(1234) 567-8901',
           'email' => 'marialis@admin.com',
           'password' => Hash::make('24698948'),
           'user_type' => 'A',
           'level_instruction'=>'Lic',
           'sex' => 'F',
           'active'=>true,
           'nationality'=>'V',
           'organization_id'=>'B',
       ]);*/
    }
}
