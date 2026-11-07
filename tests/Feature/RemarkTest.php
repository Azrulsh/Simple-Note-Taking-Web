<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Illuminate\Support\Facades\Auth;

class RemarkTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_remark(): void
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        // Create a note
        $this->actingAs($user)
            ->post(route('manage-note.StoreNote'), [
                'name' => 'Test Note',
                'description' => 'Note for remark testing.',
            ])
            ->assertSessionHasNoErrors();

        $noteId = DB::table('note_record')
            ->where('name', 'Test Note')
            ->where('user_id', $user->id)
            ->value('id');

        $this->assertNotNull($noteId);

        // Create remark through AJAX
        $response = $this->actingAs($user)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('manage-remark.SaveRemark'), [
                'description' => 'This is a test remark.',
                'note_id' => $noteId,
            ]);

        $response->assertOk();

        // Verify remark belongs to correct note and user
        $this->assertDatabaseHas('remark_record', [
            'description' => 'This is a test remark.',
            'note_id' => $noteId,
            'user_id' => $user->id,
        ]);
    }

    public function test_remark_creation_requires_description(): void
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->withHeaders([
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json',
            ])
            ->post(route('manage-remark.SaveRemark'), [
                'description' => '',
                'note_id' => 999999,
            ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('description');

        $this->assertDatabaseCount('remark_record', 0);
    }

    public function test_remark_creation_rejects_description_longer_than_255_characters(): void
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->withHeaders([
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json',
            ])
            ->post(route('manage-remark.SaveRemark'), [
                'description' => str_repeat('A', 256),
                'note_id' => 999999,
            ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('description');

        $this->assertDatabaseCount('remark_record', 0);
    }

    public function test_remark_creation_rejects_nonexistent_note(): void
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->withHeaders([
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json',
            ])
            ->post(route('manage-remark.SaveRemark'), [
                'description' => 'Valid remark description.',
                'note_id' => 999999,
            ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('note_id');

        $this->assertDatabaseCount('remark_record', 0);
    }

    public function test_authenticated_user_can_view_remarks_for_specific_note(): void
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        // Create a note
        $this->actingAs($user)
            ->post(route('manage-note.StoreNote'), [
                'name' => 'Note With Remarks',
                'description' => 'Testing remark listing.',
            ])
            ->assertSessionHasNoErrors();

        $noteId = DB::table('note_record')
            ->where('name', 'Note With Remarks')
            ->where('user_id', $user->id)
            ->value('id');

        $this->assertNotNull($noteId);

        // Create a remark through AJAX
        $this->actingAs($user)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('manage-remark.SaveRemark'), [
                'note_id' => $noteId,
                'description' => 'First test remark.',
            ])
            ->assertOk();

        // Fetch remarks using AJAX
        $response = $this->actingAs($user)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->get(route('manage-remark.ListRemark', [
                'note_id' => $noteId,
            ]));

        $response->assertOk();

        // Verify correct Blade view is returned
        $response->assertViewIs('manage-remark.ListRemark');

        // Verify remark description appears in HTML
        $response->assertSee('First test remark.');
    }

    public function test_remark_list_only_displays_remarks_for_requested_note(): void
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        // Create Note A
        $this->actingAs($user)
            ->post(route('manage-note.StoreNote'), [
                'name' => 'Note A',
                'description' => 'Description A',
            ])
            ->assertSessionHasNoErrors();

        // Create Note B
        $this->actingAs($user)
            ->post(route('manage-note.StoreNote'), [
                'name' => 'Note B',
                'description' => 'Description B',
            ])
            ->assertSessionHasNoErrors();

        $noteAId = DB::table('note_record')
            ->where('name', 'Note A')
            ->where('user_id', $user->id)
            ->value('id');

        $noteBId = DB::table('note_record')
            ->where('name', 'Note B')
            ->where('user_id', $user->id)
            ->value('id');

        $this->assertNotNull($noteAId);
        $this->assertNotNull($noteBId);

        // Create remark for Note A
        $this->actingAs($user)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('manage-remark.SaveRemark'), [
                'note_id' => $noteAId,
                'description' => 'Remark belongs to Note A',
            ])
            ->assertOk();

        // Create remark for Note B
        $this->actingAs($user)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('manage-remark.SaveRemark'), [
                'note_id' => $noteBId,
                'description' => 'Remark belongs to Note B',
            ])
            ->assertOk();

        // Fetch remarks for Note A only
        $response = $this->actingAs($user)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->get(route('manage-remark.ListRemark', [
                'note_id' => $noteAId,
            ]));

        $response->assertOk();

        // Note A remark must be visible
        $response->assertSee('Remark belongs to Note A');

        // Note B remark must NOT be visible
        $response->assertDontSee('Remark belongs to Note B');
    }

    public function test_remark_owner_can_update_remark(): void
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        // Create a note
        $this->actingAs($user)
            ->post(route('manage-note.StoreNote'), [
                'name' => 'Update Remark Note',
                'description' => 'Note for testing remark updates.',
            ])
            ->assertSessionHasNoErrors();

        $noteId = DB::table('note_record')
            ->where('name', 'Update Remark Note')
            ->where('user_id', $user->id)
            ->value('id');

        $this->assertNotNull($noteId);

        // Create a remark through AJAX
        $this->actingAs($user)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('manage-remark.SaveRemark'), [
                'note_id' => $noteId,
                'description' => 'Original Remark',
            ])
            ->assertOk();

        $remarkId = DB::table('remark_record')
            ->where('note_id', $noteId)
            ->where('description', 'Original Remark')
            ->value('id');

        $this->assertNotNull($remarkId);

        // Update remark through AJAX
        $response = $this->actingAs($user)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('manage-remark.UpdateRemark'), [
                'id' => $remarkId,
                'description' => 'Updated Remark',
            ]);

        $response->assertOk();

        // Verify updated remark
        $this->assertDatabaseHas('remark_record', [
            'id' => $remarkId,
            'description' => 'Updated Remark',
            'note_id' => $noteId,
            'user_id' => $user->id,
        ]);

        // Verify old description no longer exists
        $this->assertDatabaseMissing('remark_record', [
            'id' => $remarkId,
            'description' => 'Original Remark',
        ]);
    }

    public function test_remark_update_requires_description(): void
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        // Create note
        $this->actingAs($user)
            ->post(route('manage-note.StoreNote'), [
                'name' => 'Validation Note',
                'description' => 'Testing update validation.',
            ])
            ->assertSessionHasNoErrors();

        $noteId = DB::table('note_record')
            ->where('name', 'Validation Note')
            ->value('id');

        // Create remark
        $this->actingAs($user)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('manage-remark.SaveRemark'), [
                'note_id' => $noteId,
                'description' => 'Original Remark',
            ])
            ->assertOk();

        $remarkId = DB::table('remark_record')
            ->where('note_id', $noteId)
            ->value('id');

        // Attempt update with empty description
        $response = $this->actingAs($user)
            ->withHeaders([
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json',
            ])
            ->post(route('manage-remark.UpdateRemark'), [
                'id' => $remarkId,
                'description' => '',
            ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('description');

        $this->assertDatabaseHas('remark_record', [
            'id' => $remarkId,
            'description' => 'Original Remark',
        ]);
    }

    public function test_remark_update_rejects_description_longer_than_255_characters(): void
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        // Create note
        $this->actingAs($user)
            ->post(route('manage-note.StoreNote'), [
                'name' => 'Validation Note',
                'description' => 'Testing maximum length.',
            ])
            ->assertSessionHasNoErrors();

        $noteId = DB::table('note_record')
            ->where('name', 'Validation Note')
            ->value('id');

        // Create remark
        $this->actingAs($user)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('manage-remark.SaveRemark'), [
                'note_id' => $noteId,
                'description' => 'Original Remark',
            ])
            ->assertOk();

        $remarkId = DB::table('remark_record')
            ->where('note_id', $noteId)
            ->value('id');

        // Attempt update with 256 characters
        $response = $this->actingAs($user)
            ->withHeaders([
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json',
            ])
            ->post(route('manage-remark.UpdateRemark'), [
                'id' => $remarkId,
                'description' => str_repeat('A', 256),
            ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('description');

        $this->assertDatabaseHas('remark_record', [
            'id' => $remarkId,
            'description' => 'Original Remark',
        ]);
    }

    public function test_user_cannot_update_another_users_remark(): void
    {
        /** @var \App\Models\User $owner */
        $owner = User::factory()->create();

        /** @var \App\Models\User $otherUser */
        $otherUser = User::factory()->create();

        // User A creates a note
        $this->actingAs($owner)
            ->post(route('manage-note.StoreNote'), [
                'name' => 'Owner Note',
                'description' => 'Note for authorization testing.',
            ])
            ->assertSessionHasNoErrors();

        $noteId = DB::table('note_record')
            ->where('name', 'Owner Note')
            ->where('user_id', $owner->id)
            ->value('id');

        $this->assertNotNull($noteId);

        // User A creates a remark
        $this->actingAs($owner)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('manage-remark.SaveRemark'), [
                'note_id' => $noteId,
                'description' => 'Original Remark',
            ])
            ->assertOk();

        $remarkId = DB::table('remark_record')
            ->where('note_id', $noteId)
            ->where('user_id', $owner->id)
            ->value('id');

        $this->assertNotNull($remarkId);

        // User B attempts to update User A's remark
        $response = $this->actingAs($otherUser)
            ->withHeaders([
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json',
            ])
            ->post(route('manage-remark.UpdateRemark'), [
                'id' => $remarkId,
                'description' => 'Unauthorized Update',
            ]);

        // Authorization must reject the request
        $response->assertForbidden();

        $response->assertJsonPath(
            'error',
            'You are not authorized to update this remark.'
        );

        // Original remark must remain unchanged
        $this->assertDatabaseHas('remark_record', [
            'id' => $remarkId,
            'description' => 'Original Remark',
            'note_id' => $noteId,
            'user_id' => $owner->id,
        ]);

        $this->assertDatabaseMissing('remark_record', [
            'id' => $remarkId,
            'description' => 'Unauthorized Update',
        ]);
    }

    public function test_guest_cannot_update_remark(): void
    {
        /** @var \App\Models\User $owner */
        $owner = User::factory()->create();

        // Owner creates a note
        $this->actingAs($owner)
            ->post(route('manage-note.StoreNote'), [
                'name' => 'Protected Note',
                'description' => 'Testing guest authorization.',
            ])
            ->assertSessionHasNoErrors();

        $noteId = DB::table('note_record')
            ->where('name', 'Protected Note')
            ->where('user_id', $owner->id)
            ->value('id');

        $this->assertNotNull($noteId);

        // Owner creates a remark
        $this->actingAs($owner)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('manage-remark.SaveRemark'), [
                'note_id' => $noteId,
                'description' => 'Original Remark',
            ])
            ->assertOk();

        $remarkId = DB::table('remark_record')
            ->where('note_id', $noteId)
            ->where('user_id', $owner->id)
            ->value('id');

        $this->assertNotNull($remarkId);

        // Simulate guest
        \Illuminate\Support\Facades\Auth::logout();

        // Guest attempts to update the remark
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json',
        ])->post(route('manage-remark.UpdateRemark'), [
            'id' => $remarkId,
            'description' => 'Guest Modified Remark',
        ]);

        // Guest should be rejected by authentication middleware
        $response->assertUnauthorized();

        $this->assertGuest();

        // Original remark must remain unchanged
        $this->assertDatabaseHas('remark_record', [
            'id' => $remarkId,
            'description' => 'Original Remark',
            'note_id' => $noteId,
            'user_id' => $owner->id,
        ]);
    }

    public function test_remark_owner_can_delete_remark(): void
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        // Create a note
        $this->actingAs($user)
            ->post(route('manage-note.StoreNote'), [
                'name' => 'Delete Remark Note',
                'description' => 'Note for testing remark deletion.',
            ])
            ->assertSessionHasNoErrors();

        $noteId = DB::table('note_record')
            ->where('name', 'Delete Remark Note')
            ->where('user_id', $user->id)
            ->value('id');

        $this->assertNotNull($noteId);

        // Create a remark using AJAX
        $this->actingAs($user)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('manage-remark.SaveRemark'), [
                'note_id' => $noteId,
                'description' => 'Remark To Delete',
            ])
            ->assertOk();

        $remarkId = DB::table('remark_record')
            ->where('note_id', $noteId)
            ->where('user_id', $user->id)
            ->value('id');

        $this->assertNotNull($remarkId);

        // Delete remark using AJAX
        $response = $this->actingAs($user)
            ->withHeaders([
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json',
            ])
            ->post(route('manage-remark.DeleteRemark'), [
                'id' => $remarkId,
            ]);

        // Verify successful JSON response
        $response->assertOk();

        $response->assertJsonPath(
            'success',
            'Remark deleted successfully'
        );

        // Verify remark has been deleted
        $this->assertDatabaseMissing('remark_record', [
            'id' => $remarkId,
        ]);

        // Verify parent note still exists
        $this->assertDatabaseHas('note_record', [
            'id' => $noteId,
            'user_id' => $user->id,
        ]);
    }

    public function test_user_cannot_delete_another_users_remark(): void
    {
        /** @var \App\Models\User $owner */
        $owner = User::factory()->create();

        /** @var \App\Models\User $otherUser */
        $otherUser = User::factory()->create();

        // User A creates a note
        $this->actingAs($owner)
            ->post(route('manage-note.StoreNote'), [
                'name' => 'Owner Note',
                'description' => 'Note for delete authorization testing.',
            ])
            ->assertSessionHasNoErrors();

        $noteId = DB::table('note_record')
            ->where('name', 'Owner Note')
            ->where('user_id', $owner->id)
            ->value('id');

        $this->assertNotNull($noteId);

        // User A creates a remark
        $this->actingAs($owner)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('manage-remark.SaveRemark'), [
                'note_id' => $noteId,
                'description' => 'Original Remark',
            ])
            ->assertOk();

        $remarkId = DB::table('remark_record')
            ->where('note_id', $noteId)
            ->where('user_id', $owner->id)
            ->value('id');

        $this->assertNotNull($remarkId);

        // User B attempts to delete User A's remark
        $response = $this->actingAs($otherUser)
            ->withHeaders([
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json',
            ])
            ->post(route('manage-remark.DeleteRemark'), [
                'id' => $remarkId,
            ]);

        // Authorization must reject the request
        $response->assertForbidden();

        $response->assertJsonPath(
            'error',
            'You are not authorized to delete this remark.'
        );

        // Original remark must still exist
        $this->assertDatabaseHas('remark_record', [
            'id' => $remarkId,
            'description' => 'Original Remark',
            'note_id' => $noteId,
            'user_id' => $owner->id,
        ]);

        // Parent note must still exist
        $this->assertDatabaseHas('note_record', [
            'id' => $noteId,
            'user_id' => $owner->id,
        ]);
    }

    public function test_guest_cannot_delete_remark(): void
    {
        /** @var \App\Models\User $owner */
        $owner = User::factory()->create();

        // Owner creates a note
        $this->actingAs($owner)
            ->post(route('manage-note.StoreNote'), [
                'name' => 'Protected Note',
                'description' => 'Testing guest delete authorization.',
            ])
            ->assertSessionHasNoErrors();

        $noteId = DB::table('note_record')
            ->where('name', 'Protected Note')
            ->where('user_id', $owner->id)
            ->value('id');

        $this->assertNotNull($noteId);

        // Owner creates a remark
        $this->actingAs($owner)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('manage-remark.SaveRemark'), [
                'note_id' => $noteId,
                'description' => 'Protected Remark',
            ])
            ->assertOk();

        $remarkId = DB::table('remark_record')
            ->where('note_id', $noteId)
            ->where('user_id', $owner->id)
            ->value('id');

        $this->assertNotNull($remarkId);

        // Simulate guest
        Auth::logout();

        // Guest attempts to delete remark through AJAX
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json',
        ])->post(route('manage-remark.DeleteRemark'), [
            'id' => $remarkId,
        ]);

        // Guest must be rejected
        $response->assertUnauthorized();

        $this->assertGuest();

        // Original remark must still exist
        $this->assertDatabaseHas('remark_record', [
            'id' => $remarkId,
            'description' => 'Protected Remark',
            'note_id' => $noteId,
            'user_id' => $owner->id,
        ]);

        // Parent note must still exist
        $this->assertDatabaseHas('note_record', [
            'id' => $noteId,
            'user_id' => $owner->id,
        ]);
    }

    public function test_remark_deletion_rejects_nonexistent_id(): void
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        // Attempt to delete a remark that does not exist
        $response = $this->actingAs($user)
            ->withHeaders([
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json',
            ])
            ->post(route('manage-remark.DeleteRemark'), [
                'id' => 999999,
            ]);

        // Laravel validation should reject the invalid ID
        $response->assertUnprocessable();

        $response->assertJsonValidationErrors('id');

        // No remarks should be deleted or created
        $this->assertDatabaseCount('remark_record', 0);
    }

    public function test_remark_owner_can_check_edit_permission(): void
    {
        /** @var User $owner */
        $owner = User::factory()->create();

        $this->actingAs($owner)
            ->post(route('manage-note.StoreNote'), [
                'name' => 'Check Edit Note',
                'description' => 'Note for check edit testing.',
            ])
            ->assertSessionHasNoErrors();

        $noteId = DB::table('note_record')
            ->where('name', 'Check Edit Note')
            ->where('user_id', $owner->id)
            ->value('id');

        $this->assertNotNull($noteId);

        $this->actingAs($owner)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('manage-remark.SaveRemark'), [
                'note_id' => $noteId,
                'description' => 'Original Remark',
            ])
            ->assertOk();

        $remarkId = DB::table('remark_record')
            ->where('note_id', $noteId)
            ->where('user_id', $owner->id)
            ->value('id');

        $this->assertNotNull($remarkId);

        $response = $this->actingAs($owner)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('manage-remark.CheckEditRemark'), [
                'remark_id' => $remarkId,
            ]);

        $response->assertOk();

        $response->assertJsonPath(
            'success',
            'Permission granted to edit the remark.'
        );
    }

    public function test_other_user_cannot_check_edit_permission(): void
    {
        /** @var User $owner */
        $owner = User::factory()->create();

        /** @var User $otherUser */
        $otherUser = User::factory()->create();

        $this->actingAs($owner)
            ->post(route('manage-note.StoreNote'), [
                'name' => 'Owner Note',
                'description' => 'Authorization testing.',
            ])
            ->assertSessionHasNoErrors();

        $noteId = DB::table('note_record')
            ->where('name', 'Owner Note')
            ->where('user_id', $owner->id)
            ->value('id');

        $this->assertNotNull($noteId);

        $this->actingAs($owner)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('manage-remark.SaveRemark'), [
                'note_id' => $noteId,
                'description' => 'Protected Remark',
            ])
            ->assertOk();

        $remarkId = DB::table('remark_record')
            ->where('note_id', $noteId)
            ->where('user_id', $owner->id)
            ->value('id');

        $this->assertNotNull($remarkId);

        $response = $this->actingAs($otherUser)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('manage-remark.CheckEditRemark'), [
                'remark_id' => $remarkId,
            ]);

        $response->assertForbidden();

        $response->assertJsonPath(
            'error',
            'You do not have permission to edit this remark.'
        );

        $this->assertDatabaseHas('remark_record', [
            'id' => $remarkId,
            'description' => 'Protected Remark',
            'user_id' => $owner->id,
        ]);
    }

    public function test_guest_cannot_check_edit_permission(): void
    {
        /** @var User $owner */
        $owner = User::factory()->create();

        $this->actingAs($owner)
            ->post(route('manage-note.StoreNote'), [
                'name' => 'Guest Check Note',
                'description' => 'Testing guest access.',
            ])
            ->assertSessionHasNoErrors();

        $noteId = DB::table('note_record')
            ->where('name', 'Guest Check Note')
            ->where('user_id', $owner->id)
            ->value('id');

        $this->assertNotNull($noteId);

        $this->actingAs($owner)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('manage-remark.SaveRemark'), [
                'note_id' => $noteId,
                'description' => 'Protected Remark',
            ])
            ->assertOk();

        $remarkId = DB::table('remark_record')
            ->where('note_id', $noteId)
            ->where('user_id', $owner->id)
            ->value('id');

        $this->assertNotNull($remarkId);

        Auth::logout();

        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json',
        ])->post(route('manage-remark.CheckEditRemark'), [
            'remark_id' => $remarkId,
        ]);

        $response->assertUnauthorized();

        $this->assertGuest();

        $this->assertDatabaseHas('remark_record', [
            'id' => $remarkId,
            'description' => 'Protected Remark',
        ]);
    }

    public function test_check_edit_rejects_nonexistent_remark(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->withHeaders([
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json',
            ])
            ->post(route('manage-remark.CheckEditRemark'), [
                'remark_id' => 999999,
            ]);

        $response->assertNotFound();

        $this->assertDatabaseCount('remark_record', 0);
    }

    public function test_remark_owner_can_check_delete_permission(): void
    {
        /** @var User $owner */
        $owner = User::factory()->create();

        $this->actingAs($owner)
            ->post(route('manage-note.StoreNote'), [
                'name' => 'Check Delete Note',
                'description' => 'Note for check delete testing.',
            ])
            ->assertSessionHasNoErrors();

        $noteId = DB::table('note_record')
            ->where('name', 'Check Delete Note')
            ->where('user_id', $owner->id)
            ->value('id');

        $this->assertNotNull($noteId);

        $this->actingAs($owner)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('manage-remark.SaveRemark'), [
                'note_id' => $noteId,
                'description' => 'Original Remark',
            ])
            ->assertOk();

        $remarkId = DB::table('remark_record')
            ->where('note_id', $noteId)
            ->where('user_id', $owner->id)
            ->value('id');

        $this->assertNotNull($remarkId);

        // Owner checks delete permission
        $response = $this->actingAs($owner)
            ->withHeaders([
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json',
            ])
            ->post(route('manage-remark.CheckDeleteRemark'), [
                'remark_id' => $remarkId,
            ]);

        $response->assertOk();

        $response->assertJsonPath(
            'success',
            'Permission granted to delete the remark.'
        );

        // Check permission must not delete the remark
        $this->assertDatabaseHas('remark_record', [
            'id' => $remarkId,
            'description' => 'Original Remark',
            'user_id' => $owner->id,
        ]);
    }

    public function test_other_user_cannot_check_delete_permission(): void
    {
        /** @var User $owner */
        $owner = User::factory()->create();

        /** @var User $otherUser */
        $otherUser = User::factory()->create();

        $this->actingAs($owner)
            ->post(route('manage-note.StoreNote'), [
                'name' => 'Owner Note',
                'description' => 'Delete authorization testing.',
            ])
            ->assertSessionHasNoErrors();

        $noteId = DB::table('note_record')
            ->where('name', 'Owner Note')
            ->where('user_id', $owner->id)
            ->value('id');

        $this->assertNotNull($noteId);

        $this->actingAs($owner)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('manage-remark.SaveRemark'), [
                'note_id' => $noteId,
                'description' => 'Protected Remark',
            ])
            ->assertOk();

        $remarkId = DB::table('remark_record')
            ->where('note_id', $noteId)
            ->where('user_id', $owner->id)
            ->value('id');

        $this->assertNotNull($remarkId);

        // User B attempts to check delete permission
        $response = $this->actingAs($otherUser)
            ->withHeaders([
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json',
            ])
            ->post(route('manage-remark.CheckDeleteRemark'), [
                'remark_id' => $remarkId,
            ]);

        $response->assertForbidden();

        $response->assertJsonPath(
            'error',
            'You do not have permission to delete this remark.'
        );

        // Original remark must remain unchanged
        $this->assertDatabaseHas('remark_record', [
            'id' => $remarkId,
            'description' => 'Protected Remark',
            'note_id' => $noteId,
            'user_id' => $owner->id,
        ]);
    }

    public function test_guest_cannot_check_delete_permission(): void
    {
        /** @var User $owner */
        $owner = User::factory()->create();

        $this->actingAs($owner)
            ->post(route('manage-note.StoreNote'), [
                'name' => 'Guest Check Delete Note',
                'description' => 'Testing guest delete permission.',
            ])
            ->assertSessionHasNoErrors();

        $noteId = DB::table('note_record')
            ->where('name', 'Guest Check Delete Note')
            ->where('user_id', $owner->id)
            ->value('id');

        $this->assertNotNull($noteId);

        $this->actingAs($owner)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('manage-remark.SaveRemark'), [
                'note_id' => $noteId,
                'description' => 'Protected Remark',
            ])
            ->assertOk();

        $remarkId = DB::table('remark_record')
            ->where('note_id', $noteId)
            ->where('user_id', $owner->id)
            ->value('id');

        $this->assertNotNull($remarkId);

        // Simulate guest
        Auth::logout();

        // Guest attempts to check delete permission
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json',
        ])->post(route('manage-remark.CheckDeleteRemark'), [
            'remark_id' => $remarkId,
        ]);

        $response->assertUnauthorized();

        $this->assertGuest();

        // Original remark must still exist
        $this->assertDatabaseHas('remark_record', [
            'id' => $remarkId,
            'description' => 'Protected Remark',
            'user_id' => $owner->id,
        ]);
    }

    public function test_check_delete_rejects_nonexistent_remark(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->withHeaders([
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json',
            ])
            ->post(route('manage-remark.CheckDeleteRemark'), [
                'remark_id' => 999999,
            ]);

        $response->assertNotFound();

        $this->assertDatabaseCount('remark_record', 0);
    }

    public function test_save_remark_rejects_non_ajax_request(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('manage-remark.SaveRemark'), [
                'description' => 'Test Remark',
                'note_id' => 999999,
            ]);

        $response->assertBadRequest();
        $response->assertJsonPath('error', 'AJAX request required.');

        $this->assertDatabaseCount('remark_record', 0);
    }

    public function test_update_remark_rejects_non_ajax_request(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('manage-remark.UpdateRemark'), [
                'id' => 999999,
                'description' => 'Updated Remark',
            ]);

        $response->assertBadRequest();
        $response->assertJsonPath('error', 'AJAX request required.');
    }

    public function test_delete_remark_rejects_non_ajax_request(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('manage-remark.DeleteRemark'), [
                'id' => 999999,
            ]);

        $response->assertBadRequest();
        $response->assertJsonPath('error', 'AJAX request required.');
    }

    public function test_remark_list_requires_note_id(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->withHeaders([
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json',
            ])
            ->get(route('manage-remark.ListRemark'));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('note_id');
    }

    public function test_remark_list_rejects_nonexistent_note_id(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->withHeaders([
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json',
            ])
            ->get(route('manage-remark.ListRemark', [
                'note_id' => 999999,
            ]));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('note_id');
    }
}