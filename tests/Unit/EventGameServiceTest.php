<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Organizer;
use App\Models\Event;
use App\Models\Province;
use App\Models\District;
use App\Models\Ward;
use App\Models\EventGame;
use App\Utils\Constants\EventUserHistoryStatus;
use App\Utils\Constants\RoleUser;
use App\Services\EventGameService;
use App\Models\EventUserHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EventGameServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_getCheckintUserEvent_returns_participated_users()
    {
        // Mock data creation or use existing if possible, 
        // strictly for this environment we will try to create temporary data if DB connection allows,
        // otherwise we might need to rely on manual verification or mocks.
        // Assuming we can write to DB for tests.

        // 1. Create Organizer
        $organizer = new Organizer();
        $organizer->name = 'Test Organizer';
        $organizer->save();

        // 1.1 Create Location Data
        $province = new Province();
        $province->code = '01';
        $province->name = 'Test Province';
        $province->save();

        $district = new District();
        $district->code = '0101';
        $district->province_code = '01';
        $district->name = 'Test District';
        $district->save();

        $ward = new Ward();
        $ward->code = '010101';
        $ward->district_code = '0101';
        $ward->name = 'Test Ward';
        $ward->save();

        // 2. Create Event
        $event = new Event();
        $event->name = 'Test Event';
        $event->short_description = 'Test Short Description';
        $event->description = 'Test Description';
        $event->day_represent = now();
        $event->start_time = now();
        $event->end_time = now()->addDays(1);
        $event->status = 1;
        $event->address = 'Test Address';
        $event->province_code = '01';
        $event->district_code = '0101';
        $event->ward_code = '010101';
        $event->latitude = 0;
        $event->longitude = 0;
        $event->organizer_id = $organizer->id;
        $event->save();

        // 2. Create Game linked to Event
        $game = new EventGame();
        $game->event_id = $event->id;
        $game->name = 'Test Game';
        $game->game_type = 1; // Assuming integer or string, checking later if needed
        $game->description = 'Test Game Description';
        $game->config_game = [];
        $game->save();

        // 3. Create Users
        $user1 = new User();
        $user1->name = 'User One';
        $user1->email = 'user1_' . uniqid() . '@test.com';
        $user1->password = bcrypt('password');
        $user1->role = RoleUser::CUSTOMER->value;
        $user1->avatar_path = 'path/to/avatar1.jpg';
        $user1->save();

        $user2 = new User();
        $user2->name = 'User Two';
        $user2->email = 'user2_' . uniqid() . '@test.com';
        $user2->password = bcrypt('password');
        $user2->role = RoleUser::CUSTOMER->value;
        $user2->avatar_path = 'path/to/avatar2.jpg';
        $user2->save();

        // 4. Create History
        // User 1 Participated
        EventUserHistory::create([
            'event_id' => $event->id,
            'user_id' => $user1->id,
            'status' => EventUserHistoryStatus::PARTICIPATED->value
        ]);

        // User 2 Booked (Not Participated)
        EventUserHistory::create([
            'event_id' => $event->id,
            'user_id' => $user2->id,
            'status' => EventUserHistoryStatus::BOOKED->value
        ]);

        // 5. Call Service
        $service = new EventGameService();
        $result = $service->getCheckintUserEvent($game);

        // 6. Assertions
        $this->assertTrue($result['status']);
        $this->assertEquals(1, $result['data']->total());
        $this->assertEquals($user1->id, $result['data']->first()->id);

        // Cleanup
        $event->delete();
        $game->delete();
        $user1->delete();
        $user2->delete();
    }
}
