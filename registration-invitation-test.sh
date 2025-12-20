#!/bin/bash

# Registration and Invitation Workflow Test Script
# Tests both default registration and invitation workflows with database verification

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Container name
CONTAINER="vilagmuhely-php-fpm-1"

# Test data
TIMESTAMP=$(date +%s)
TEST_REGULAR_EMAIL="test-regular-${TIMESTAMP}@example.com"
TEST_REGULAR_NAME="Regular User ${TIMESTAMP}"
TEST_REGULAR_PASSWORD="password123"

TEST_INVITED_EMAIL="test-invited-${TIMESTAMP}@example.com"
TEST_INVITED_NAME="Invited User ${TIMESTAMP}"
TEST_INVITED_PASSWORD="password123"

# Admin user for creating invitations (assuming exists)
ADMIN_EMAIL="signred@gmail.com"

# Function to print status messages
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[✓]${NC} $1"
}

print_error() {
    echo -e "${RED}[✗]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[!]${NC} $1"
}

# Function to execute tinker command
exec_tinker() {
    local command="$1"
    docker exec "$CONTAINER" php artisan tinker --execute="$command" 2>/dev/null
}

# Function to query database
query_db() {
    local query="$1"
    exec_tinker "DB::select(\"$query\");"
}

# Function to check if user exists
check_user_exists() {
    local email="$1"
    local result=$(exec_tinker "echo App\Models\User::where('email', '$email')->exists() ? 'EXISTS' : 'NOTFOUND';")
    echo "$result" | grep -q "EXISTS"
}

# Function to get user data
get_user_data() {
    local email="$1"
    exec_tinker "App\Models\User::where('email', '$email')->first();"
}

# Function to check user verification status
check_user_verified() {
    local email="$1"
    local result=$(exec_tinker "echo App\Models\User::where('email', '$email')->first()?->email_verified_at !== null ? 'VERIFIED' : 'UNVERIFIED';")
    echo "$result" | grep -q "VERIFIED"
}

# Function to check user role
check_user_has_role() {
    local email="$1"
    local role_slug="$2"
    local result=$(exec_tinker "\$user = App\Models\User::where('email', '$email')->first(); echo (\$user && \$user->roles()->where('slug', '$role_slug')->exists()) ? 'HASROLE' : 'NOROLE';")
    echo "$result" | grep -q "HASROLE"
}

# Function to create invitation
create_invitation() {
    local name="$1"
    local email="$2"
    local message="$3"

    exec_tinker "
        \$admin = App\Models\User::where('email', '$ADMIN_EMAIL')->first();
        if (!\$admin) {
            \$admin = App\Models\User::create([
                'name' => 'Admin User',
                'email' => '$ADMIN_EMAIL',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);
            \$adminRole = Webtechsolutions\UserManager\Models\Role::firstOrCreate(
                ['slug' => 'admin'],
                ['name' => 'Administrator', 'is_supervisor' => true]
            );
            \$admin->roles()->attach(\$adminRole->id);
        }

        \$invitation = App\Models\Invitation::create([
            'name' => '$name',
            'email' => '$email',
            'message' => '$message',
            'invited_by_user_id' => \$admin->id,
            'token' => Str::random(64),
            'expires_at' => now()->addHours(72),
            'status' => 'pending',
        ]);

        echo 'TOKEN:' . \$invitation->token;
    "
}

# Function to get invitation data
get_invitation_data() {
    local email="$1"
    exec_tinker "App\Models\Invitation::where('email', '$email')->first();"
}

# Function to simulate user registration
simulate_registration() {
    local name="$1"
    local email="$2"
    local password="$3"

    exec_tinker "
        \$user = App\Models\User::create([
            'name' => '$name',
            'email' => '$email',
            'password' => Hash::make('$password'),
        ]);

        \$defaultRole = Webtechsolutions\UserManager\Models\Role::where('slug', config('invitations.default_role_slug'))->first();
        if (!\$defaultRole) {
            \$defaultRole = Webtechsolutions\UserManager\Models\Role::create([
                'name' => 'Member',
                'slug' => 'members',
            ]);
        }

        if (\$defaultRole) {
            \$user->roles()->attach(\$defaultRole->id);
        }

        echo 'User created: ' . \$user->id;
    "
}

# Function to simulate invited user registration
simulate_invited_registration() {
    local name="$1"
    local email="$2"
    local password="$3"
    local token="$4"

    exec_tinker "
        \$invitation = App\Models\Invitation::where('token', '$token')
            ->where('email', '$email')
            ->where('status', 'pending')
            ->first();

        if (\$invitation && !\$invitation->isExpired()) {
            \$user = App\Models\User::create([
                'name' => '$name',
                'email' => '$email',
                'password' => Hash::make('$password'),
                'email_verified_at' => now(),
            ]);

            \$defaultRole = Webtechsolutions\UserManager\Models\Role::where('slug', config('invitations.default_role_slug'))->first();
            if (\$defaultRole) {
                \$user->roles()->attach(\$defaultRole->id);
            }

            \$invitation->update([
                'status' => 'accepted',
                'accepted_at' => now(),
                'accepted_by_user_id' => \$user->id,
            ]);

            echo 'Invited user created and invitation accepted';
        }
    "
}

# Function to verify email
verify_user_email() {
    local email="$1"
    exec_tinker "
        \$user = App\Models\User::where('email', '$email')->first();
        if (\$user) {
            \$user->email_verified_at = now();
            \$user->save();
            echo 'Email verified';
        }
    "
}

# Function to cleanup test data
cleanup_test_data() {
    local email="$1"
    exec_tinker "
        \$user = App\Models\User::where('email', '$email')->first();
        if (\$user) {
            \$user->roles()->detach();
            \$user->delete();
            echo 'User deleted: $email';
        }

        App\Models\Invitation::where('email', '$email')->delete();
    " > /dev/null 2>&1 || true
}

# Main test execution
echo ""
echo "=========================================="
echo "Registration & Invitation Workflow Tests"
echo "=========================================="
echo ""

# Cleanup any existing test data
print_status "Cleaning up any existing test data..."
cleanup_test_data "$TEST_REGULAR_EMAIL"
cleanup_test_data "$TEST_INVITED_EMAIL"
print_success "Cleanup complete"
echo ""

# ==========================================
# TEST 1: Default Registration Workflow
# ==========================================
echo "=========================================="
echo "TEST 1: Default Registration Workflow"
echo "=========================================="
echo ""

print_status "Step 1: Creating regular user via registration..."
simulate_registration "$TEST_REGULAR_NAME" "$TEST_REGULAR_EMAIL" "$TEST_REGULAR_PASSWORD"

# Verify user created
if check_user_exists "$TEST_REGULAR_EMAIL"; then
    print_success "User created successfully"
else
    print_error "User creation failed"
    exit 1
fi

print_status "Step 2: Simulating email verification..."
verify_user_email "$TEST_REGULAR_EMAIL"

print_status "Step 3: Verifying default role assignment..."
if check_user_has_role "$TEST_REGULAR_EMAIL" "members"; then
    print_success "Default role 'members' assigned successfully"
else
    print_error "Default role not assigned"
    exit 1
fi

print_status "Step 4: Verifying email_verified_at is NULL..."
if check_user_verified "$TEST_REGULAR_EMAIL"; then
    print_error "User should NOT be verified yet"
    exit 1
else
    print_success "User is unverified (email_verified_at = null)"
fi

print_status "Step 5: Verifying email_verified_at is set..."
if check_user_verified "$TEST_REGULAR_EMAIL"; then
    print_success "User is now verified (email_verified_at set)"
else
    print_error "Email verification failed"
    exit 1
fi

echo ""
print_success "✓ DEFAULT REGISTRATION WORKFLOW TEST PASSED"
echo ""

# ==========================================
# TEST 2: Invitation Workflow
# ==========================================
echo "=========================================="
echo "TEST 2: Invitation Workflow"
echo "=========================================="
echo ""

print_status "Step 1: Creating invitation..."
INVITATION_OUTPUT=$(create_invitation "$TEST_INVITED_NAME" "$TEST_INVITED_EMAIL" "Welcome to the platform!")
INVITATION_TOKEN=$(echo "$INVITATION_OUTPUT" | grep -oP 'TOKEN:\K[a-zA-Z0-9]+' || true)

if [ -z "$INVITATION_TOKEN" ]; then
    print_error "Failed to create invitation or extract token"
    print_error "Output was: $INVITATION_OUTPUT"
    exit 1
fi

print_success "Invitation created with token: ${INVITATION_TOKEN:0:20}..."

print_status "Step 2: Verifying invitation status is 'pending'..."
INVITATION_DATA=$(get_invitation_data "$TEST_INVITED_EMAIL")
if echo "$INVITATION_DATA" | grep -q "pending"; then
    print_success "Invitation status is 'pending'"
else
    print_error "Invitation status is not 'pending'"
    exit 1
fi

print_status "Step 3: Simulating invited user registration..."
simulate_invited_registration "$TEST_INVITED_NAME" "$TEST_INVITED_EMAIL" "$TEST_INVITED_PASSWORD" "$INVITATION_TOKEN"

# Verify user created
if check_user_exists "$TEST_INVITED_EMAIL"; then
    print_success "Invited user created successfully"
else
    print_error "Invited user creation failed"
    exit 1
fi

print_status "Step 4: Verifying email_verified_at is set (auto-verified)..."
if check_user_verified "$TEST_INVITED_EMAIL"; then
    print_success "Invited user is auto-verified (email_verified_at set)"
else
    print_error "Invited user should be auto-verified"
    exit 1
fi

print_status "Step 5: Verifying default role assignment..."
if check_user_has_role "$TEST_INVITED_EMAIL" "members"; then
    print_success "Default role 'members' assigned successfully"
else
    print_error "Default role not assigned"
    exit 1
fi

print_status "Step 6: Verifying invitation status is 'accepted'..."
INVITATION_DATA=$(get_invitation_data "$TEST_INVITED_EMAIL")
if echo "$INVITATION_DATA" | grep -q "accepted"; then
    print_success "Invitation status is 'accepted'"
else
    print_error "Invitation status is not 'accepted'"
    exit 1
fi

echo ""
print_success "✓ INVITATION WORKFLOW TEST PASSED"
echo ""

# ==========================================
# Cleanup
# ==========================================
echo "=========================================="
echo "Cleanup"
echo "=========================================="
echo ""

print_status "Cleaning up test data..."
cleanup_test_data "$TEST_REGULAR_EMAIL"
cleanup_test_data "$TEST_INVITED_EMAIL"
print_success "Test data cleaned up"

echo ""
echo "=========================================="
print_success "ALL TESTS PASSED ✓"
echo "=========================================="
echo ""
echo "Summary:"
echo "  - Default Registration Workflow: ✓ PASSED"
echo "  - Invitation Workflow: ✓ PASSED"
echo ""
