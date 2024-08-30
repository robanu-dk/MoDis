<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
use App\Models\UserEvent;
use App\Models\Video;
use App\Models\VideoCategory;
use App\Models\Weight;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Nette\Utils\Random;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::create([
            'name' => 'Test User Pendamping',
            'email' => 'bestrobanu@gmail.com',
            'username' => 'pendamping',
            'gender' => 1,
            'role' => 1,
            'password' => bcrypt('12345'),
        ]);

        User::create([
            'name' => 'Test User Pengguna 1',
            'email' => 'pengguna@example.com',
            'username' => 'pengguna1',
            'gender' => 0,
            'role' => 0,
            'password' => bcrypt('12345'),
        ]);

        User::create([
            'name' => 'Test User Pengguna 2',
            'email' => 'pengguna2@example.com',
            'username' => 'pengguna2',
            'gender' => 1,
            'role' => 0,
            'password' => bcrypt('12345'),
        ]);

        User::create([
            'name' => 'Test User Pengguna 3',
            'email' => 'pengguna3@example.com',
            'username' => 'pengguna3',
            'gender' => 0,
            'role' => 0,
            'password' => bcrypt('12345'),
        ]);

        VideoCategory::create([
            'name' => 'Motivasi'
        ]);

        VideoCategory::create([
            'name' => 'Liburan'
        ]);

        VideoCategory::create([
            'name' => 'Bahasa Isyarat'
        ]);

        VideoCategory::create([
            'name' => 'Yoga'
        ]);

        for ($i=0; $i < 20; $i++) {
            Video::create([
                'id_user' => 1,
                'id_video_category' => rand(1,4),
                'title' => 'Video ' . $i,
                'thumbnail' => 'thumbnail/video' . $i . '.png',
                'video' => 'video/video' . $i . '.mp4',
                'description' => 'test ' .$i,
            ]);
        }


        Event::create([
            'name' => 'Senam Yoga',
            'date' => date('Y-m-d'),
            'start_time' => '10:00',
            'end_time' => '12:00',
            'created_by_guide' => 1,
        ]);

        Event::create([
            'name' => 'Senam Yoga',
            'date' => date('Y-m-d'),
            'start_time' => '13:00',
            'end_time' => '14:30',
            'note' => 'Sesi 2',
        ]);

        Event::create([
            'name' => 'Seminar',
            'date' => date('Y-m-d'),
            'start_time' => '13:00',
            'end_time' => '14:30',
            'note' => 'Sesi 2',
            'created_by_guide' => 1,
        ]);

        Event::create([
            'name' => 'Olahraga',
            'date' => date('Y-m-d'),
            'start_time' => '08:00',
            'end_time' => '10:30',
            'note' => 'Sesi 2',
        ]);

        UserEvent::create([
            'id_user' => '2',
            'id_event' => '1'
        ]);

        UserEvent::create([
            'id_user' => '2',
            'id_event' => '2'
        ]);

        UserEvent::create([
            'id_user' => '2',
            'id_event' => '3'
        ]);

        UserEvent::create([
            'id_user' => '2',
            'id_event' => '4'
        ]);

        UserEvent::create([
            'id_user' => '4',
            'id_event' => '1'
        ]);

        UserEvent::create([
            'id_user' => '3',
            'id_event' => '2'
        ]);

        Weight::create([
           'id_user' => '1',
           'weight' => 40,
           'date' => '2024-04-01',
        ]);

        Weight::create([
           'id_user' => '1',
           'weight' => 52,
           'date' => '2024-03-01',
        ]);

        Weight::create([
           'id_user' => '1',
           'weight' => 55,
           'date' => '2024-02-01',
        ]);

        Weight::create([
           'id_user' => '1',
           'weight' => 50,
           'date' => '2024-01-01',
        ]);

        Weight::create([
           'id_user' => '1',
           'weight' => 45,
           'date' => '2023-12-01',
        ]);

        Weight::create([
           'id_user' => '1',
           'weight' => 48,
           'date' => '2023-11-01',
        ]);

        Weight::create([
           'id_user' => '1',
           'weight' => 44,
           'date' => '2023-10-01',
        ]);

        Weight::create([
           'id_user' => '2',
           'weight' => 44,
           'date' => '2024-04-01',
        ]);

        Weight::create([
           'id_user' => '2',
           'weight' => 46,
           'date' => '2024-02-01',
        ]);

        Weight::create([
           'id_user' => '2',
           'weight' => 44,
           'date' => '2024-01-01',
        ]);

        Weight::create([
           'id_user' => '2',
           'weight' => 46.5,
           'date' => '2023-11-01',
        ]);

        Weight::create([
           'id_user' => '3',
           'weight' => 46,
           'date' => '2024-01-01',
        ]);

        Weight::create([
           'id_user' => '4',
           'weight' => 48,
           'date' => '2023-11-01',
        ]);
    }
}
