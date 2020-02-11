<?php

use Illuminate\Database\Seeder;

class UsuarioAdministradorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'name' => 'Supervisores',
            'email' => 'supervisores@gmail.com',
            'password' => bcrypt('supervisores')
        ]);

        DB::table('user_rol')->insert([
            'rol_id' => 1,
            'user_id' => 1,
            'state' => 1
        ]);
        
        DB::table('user_rol')->insert([
            'rol_id' => 2,
            'user_id' => 2,
            'state' => 1
        ]);
    }
}
