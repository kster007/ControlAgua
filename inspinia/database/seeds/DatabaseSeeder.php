<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //$this->call(UsersSeeder::class);
        //$this->call(CompanySeeder::class);
        //$this->call(StatesSeeder::class);
        //$this->call(MunicipalitiesSeeder::class);
        //$this->call(DiscountsSeeder::class);
        $this->call(ChargesSeeder::class);
    }
}