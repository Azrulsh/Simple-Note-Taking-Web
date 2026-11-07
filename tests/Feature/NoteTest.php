<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class NoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_note(): void
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('manage-note.StoreNote'), [
                'name' => 'Test Note',
                'description' => 'This is a test note.',
            ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('note_record', [
            'name' => 'Test Note',
            'description' => 'This is a test note.',
            'user_id' => $user->id,
        ]);
    }

    public function test_guest_cannot_create_note(): void
    {
        $response = $this->post(route('manage-note.StoreNote'), [
            'name' => 'Guest Note',
            'description' => 'Guest should not create notes.',
        ]);

        $response->assertRedirect(route('login'));

        $this->assertDatabaseMissing('note_record', [
            'name' => 'Guest Note',
        ]);
    }

    public function test_note_creation_requires_name_and_description(): void
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('manage-note.StoreNote'), [
                'name' => '',
                'description' => '',
            ]);

        $response->assertSessionHasErrors([
            'name',
            'description',
        ]);
    }

    public function test_note_creation_rejects_input_longer_than_255_characters(): void
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('manage-note.StoreNote'), [
                'name' => str_repeat('A', 256),
                'description' => str_repeat('B', 256),
            ]);

        $response->assertSessionHasErrors([
            'name',
            'description',
        ]);
    }

    public function test_authenticated_user_can_view_note_list(): void
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('manage-note.IndexNote'));

        $response->assertOk();
    }

    public function test_guest_cannot_view_note_list(): void
    {
        $response = $this->get(route('manage-note.IndexNote'));

        $response->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_authenticated_user_can_view_specific_note(): void
{
    /** @var \App\Models\User $user */
    $user = User::factory()->create();

    $createResponse = $this->actingAs($user)
        ->post(route('manage-note.StoreNote'), [
            'name' => 'Specific Test Note',
            'description' => 'Testing specific note details.',
        ]);

    $createResponse->assertSessionHasNoErrors();

    $noteId = DB::table('note_record')
        ->where('name', 'Specific Test Note')
        ->value('id');

    $response = $this->actingAs($user)
        ->get(route('manage-note.ListAllNote', ['id' => $noteId]));

    $response->assertOk();
    $response->assertSee('Specific Test Note');
    $response->assertSee('Testing specific note details.');
}

    public function test_viewing_nonexistent_note_returns_not_found(): void
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('manage-note.ListAllNote', ['id' => 999999]));

        $response->assertNotFound();
    }

    public function test_note_owner_can_update_note(): void
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        // Create a note
        $this->actingAs($user)
            ->post(route('manage-note.StoreNote'), [
                'name' => 'Original Note',
                'description' => 'Original description.',
            ]);

        $noteId = DB::table('note_record')
            ->where('name', 'Original Note')
            ->value('id');

        $this->assertNotNull($noteId);

        // Update the note
        $response = $this->actingAs($user)
            ->put(route('manage-note.UpdateNote', ['note' => $noteId]), [
                'name' => 'Updated Note',
                'description' => 'Updated description.',
            ]);

        $response->assertSessionHasNoErrors();

        // Verify updated data
        $this->assertDatabaseHas('note_record', [
            'id' => $noteId,
            'name' => 'Updated Note',
            'description' => 'Updated description.',
            'user_id' => $user->id,
        ]);

        // Verify old data no longer exists
        $this->assertDatabaseMissing('note_record', [
            'id' => $noteId,
            'name' => 'Original Note',
        ]);
    }

    public function test_note_update_requires_name_and_description(): void
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('manage-note.StoreNote'), [
                'name' => 'Original Note',
                'description' => 'Original description.',
            ]);

        $noteId = DB::table('note_record')
            ->where('name', 'Original Note')
            ->value('id');

        $response = $this->actingAs($user)
            ->put(route('manage-note.UpdateNote', ['note' => $noteId]), [
                'name' => '',
                'description' => '',
            ]);

        $response->assertSessionHasErrors(['name', 'description']);

        $this->assertDatabaseHas('note_record', [
            'id' => $noteId,
            'name' => 'Original Note',
            'description' => 'Original description.',
        ]);
    }

    public function test_note_update_rejects_input_longer_than_255_characters(): void
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('manage-note.StoreNote'), [
                'name' => 'Original Note',
                'description' => 'Original description.',
            ]);

        $noteId = DB::table('note_record')
            ->where('name', 'Original Note')
            ->value('id');

        $response = $this->actingAs($user)
            ->put(route('manage-note.UpdateNote', ['note' => $noteId]), [
                'name' => str_repeat('A', 256),
                'description' => str_repeat('B', 256),
            ]);

        $response->assertSessionHasErrors(['name', 'description']);

        $this->assertDatabaseHas('note_record', [
            'id' => $noteId,
            'name' => 'Original Note',
            'description' => 'Original description.',
        ]);
    }

    public function test_user_cannot_update_another_users_note(): void
    {
        /** @var \App\Models\User $owner */
        $owner = User::factory()->create();

        /** @var \App\Models\User $otherUser */
        $otherUser = User::factory()->create();

        // User A creates a note
        $this->actingAs($owner)
            ->post(route('manage-note.StoreNote'), [
                'name' => 'Owner Note',
                'description' => 'Original description.',
            ]);

        $noteId = DB::table('note_record')
            ->where('name', 'Owner Note')
            ->value('id');

        $this->assertNotNull($noteId);

        // User B attempts to update User A's note
        $response = $this->actingAs($otherUser)
            ->put(route('manage-note.UpdateNote', ['note' => $noteId]), [
                'name' => 'Hacked Note',
                'description' => 'Modified by another user.',
            ]);

        // Unauthorized update should be rejected
        $this->assertContains($response->status(), [302, 403]);

        // Original note must remain unchanged
        $this->assertDatabaseHas('note_record', [
            'id' => $noteId,
            'name' => 'Owner Note',
            'description' => 'Original description.',
            'user_id' => $owner->id,
        ]);

        $this->assertDatabaseMissing('note_record', [
            'id' => $noteId,
            'name' => 'Hacked Note',
        ]);
    }

    public function test_guest_cannot_update_note(): void
    {
        /** @var \App\Models\User $owner */
        $owner = User::factory()->create();

        // Owner creates a note
        $this->actingAs($owner)
            ->post(route('manage-note.StoreNote'), [
                'name' => 'Original Note',
                'description' => 'Original description.',
            ]);

        $noteId = DB::table('note_record')
            ->where('name', 'Original Note')
            ->value('id');

        $this->assertNotNull($noteId);

        // Simulate guest
        Auth::logout();

        // Guest attempts to update the note
        $response = $this->put(
            route('manage-note.UpdateNote', ['note' => $noteId]),
            [
                'name' => 'Guest Modified Note',
                'description' => 'Modified without login.',
            ]
        );

        // Guest should be redirected to login
        $response->assertRedirect(route('login'));

        $this->assertGuest();

        // Original data must remain unchanged
        $this->assertDatabaseHas('note_record', [
            'id' => $noteId,
            'name' => 'Original Note',
            'description' => 'Original description.',
            'user_id' => $owner->id,
        ]);
    }

    public function test_note_owner_can_delete_note(): void
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        // Create a note
        $createResponse = $this->actingAs($user)
            ->post(route('manage-note.StoreNote'), [
                'name' => 'Note To Delete',
                'description' => 'This note will be deleted.',
            ]);

        $createResponse->assertSessionHasNoErrors();

        // Retrieve note ID
        $noteId = DB::table('note_record')
            ->where('name', 'Note To Delete')
            ->where('user_id', $user->id)
            ->value('id');

        $this->assertNotNull($noteId);

        // Owner deletes the note
        $response = $this->actingAs($user)
            ->delete(route('manage-note.DeleteNote', ['id' => $noteId]));

        $response->assertSessionHasNoErrors();

        // Verify note no longer exists
        $this->assertDatabaseMissing('note_record', [
            'id' => $noteId,
        ]);
    }

    public function test_user_cannot_delete_another_users_note(): void
    {
        /** @var \App\Models\User $owner */
        $owner = User::factory()->create();

        /** @var \App\Models\User $otherUser */
        $otherUser = User::factory()->create();

        // User A creates a note
        $this->actingAs($owner)
            ->post(route('manage-note.StoreNote'), [
                'name' => 'Owner Note',
                'description' => 'This note belongs to User A.',
            ]);

        $noteId = DB::table('note_record')
            ->where('name', 'Owner Note')
            ->where('user_id', $owner->id)
            ->value('id');

        $this->assertNotNull($noteId);

        // User B attempts to delete User A's note
        $response = $this->actingAs($otherUser)
            ->delete(route('manage-note.DeleteNote', [
                'id' => $noteId,
            ]));

        // Unauthorized deletion must be rejected
        $this->assertContains($response->status(), [302, 403]);

        // Verify that the original note still exists
        $this->assertDatabaseHas('note_record', [
            'id' => $noteId,
            'name' => 'Owner Note',
            'description' => 'This note belongs to User A.',
            'user_id' => $owner->id,
        ]);
    }

    public function test_guest_cannot_delete_note(): void
    {
        /** @var \App\Models\User $owner */
        $owner = User::factory()->create();

        // Owner creates a note
        $this->actingAs($owner)
            ->post(route('manage-note.StoreNote'), [
                'name' => 'Protected Note',
                'description' => 'This note cannot be deleted by guests.',
            ]);

        $noteId = DB::table('note_record')
            ->where('name', 'Protected Note')
            ->where('user_id', $owner->id)
            ->value('id');

        $this->assertNotNull($noteId);

        // Simulate guest
        Auth::logout();

        // Guest attempts to delete the note
        $response = $this->delete(
            route('manage-note.DeleteNote', ['id' => $noteId])
        );

        // Guest should be redirected to login
        $response->assertRedirect(route('login'));

        $this->assertGuest();

        // Original note must still exist
        $this->assertDatabaseHas('note_record', [
            'id' => $noteId,
            'name' => 'Protected Note',
            'description' => 'This note cannot be deleted by guests.',
            'user_id' => $owner->id,
        ]);
    }

    public function test_deleting_note_also_deletes_its_remarks(): void
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        // Create a note through the application
        $this->actingAs($user)
            ->post(route('manage-note.StoreNote'), [
                'name' => 'Note With Remarks',
                'description' => 'Testing cascade delete.',
            ])
            ->assertSessionHasNoErrors();

        $noteId = DB::table('note_record')
            ->where('name', 'Note With Remarks')
            ->where('user_id', $user->id)
            ->value('id');

        $this->assertNotNull($noteId);

        // Create two remarks belonging to this note
        DB::table('remark_record')->insert([
            [
                'note_id' => $noteId,
                'user_id' => $user->id,
                'description' => 'First remark',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'note_id' => $noteId,
                'user_id' => $user->id,
                'description' => 'Second remark',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Verify remarks exist before deleting the note
        $this->assertDatabaseCount('remark_record', 2);

        // Delete the parent note
        $response = $this->actingAs($user)
            ->delete(route('manage-note.DeleteNote', ['id' => $noteId]));

        $response->assertSessionHasNoErrors();

        // Parent note must be deleted
        $this->assertDatabaseMissing('note_record', [
            'id' => $noteId,
        ]);

        // Both related remarks must also be deleted
        $this->assertDatabaseMissing('remark_record', [
            'note_id' => $noteId,
        ]);

        $this->assertDatabaseCount('remark_record', 0);
    }
}