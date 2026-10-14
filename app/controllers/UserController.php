<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

/**
 * UserController
 *
 * RESTful user resource using PHP 8 attribute-based routing.
 *
 * Routes (prefix from #[Route('/users')] on the class):
 *   GET    /users          -> index()    list all users
 *   GET    /users/{id}     -> show()     get one user  [named: users.show]
 *   POST   /users          -> store()    create a user
 *   PUT    /users/{id}     -> update()   full update
 *   PATCH  /users/{id}     -> update()   partial update (same handler)
 *   DELETE /users/{id}     -> destroy()  delete a user
 *
 * Persistence: runtime/users_db.json (file-based store that survives
 * across PHP dev-server requests). Swap read_db()/write_db() for a
 * real Model when you add a database.
 */
#[Route('/users')]
class UserController extends Controller
{
    // ----------------------------------------------------------------
    // Flat-file store helpers
    // ----------------------------------------------------------------

    /** Absolute path to the JSON store. */
    private function db_path(): string
    {
        // APP_DIR is defined by LavaLust as the app/ directory.
        // runtime/ sits one level up alongside app/.
        return rtrim(APP_DIR, '/\\') . '/../runtime/users_db.json';
    }

    /**
     * Read the entire store from disk.
     * Returns ['next_id' => int, 'rows' => [id => row, ...]]
     */
    private function read_db(): array
    {
        $path = $this->db_path();

        if (!file_exists($path)) {
            return ['next_id' => 1, 'rows' => []];
        }

        $data = json_decode(file_get_contents($path), true);

        return is_array($data) ? $data : ['next_id' => 1, 'rows' => []];
    }

    /** Persist the store back to disk (pretty-printed for readability). */
    private function write_db(array $data): void
    {
        file_put_contents(
            $this->db_path(),
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

    // ----------------------------------------------------------------
    // GET /users
    // ----------------------------------------------------------------

    #[Get('/')]
    public function index()
    {
        $db = $this->read_db();

        $this->response->send_json_success(
            array_values($db['rows']),
            'Users retrieved successfully'
        );
    }

    // ----------------------------------------------------------------
    // GET /users/{id}
    // ----------------------------------------------------------------

    #[Get('/{id}')]
    #[Name('users.show')]
    public function show($id)
    {
        $id  = (int) $id;
        $db  = $this->read_db();
        $row = $db['rows'][$id] ?? null;

        if ($row === null) {
            $this->response->send_json_error("User $id not found", 404);
            return;
        }

        $this->response->send_json_success($row, 'User retrieved successfully');
    }

    // ----------------------------------------------------------------
    // POST /users
    // ----------------------------------------------------------------

    #[Post('/')]
    public function store()
    {
        $body   = $this->request->json();
        $db     = $this->read_db();
        $errors = [];

        // --- validate ---
        if (empty($body['name'])) {
            $errors['name'] = 'The name field is required.';
        }

        if (empty($body['email'])) {
            $errors['email'] = 'The email field is required.';
        } elseif (!filter_var($body['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'The email must be a valid email address.';
        } else {
            foreach ($db['rows'] as $row) {
                if (strtolower($row['email']) === strtolower($body['email'])) {
                    $errors['email'] = 'This email is already taken.';
                    break;
                }
            }
        }

        if (!empty($errors)) {
            $this->response->send_json_validation($errors);
            return;
        }

        // --- persist ---
        $id  = (int) $db['next_id'];
        $row = [
            'id'    => $id,
            'name'  => trim($body['name']),
            'email' => strtolower(trim($body['email'])),
            'role'  => isset($body['role']) ? trim($body['role']) : 'user',
        ];

        $db['rows'][$id] = $row;
        $db['next_id']   = $id + 1;
        $this->write_db($db);

        $this->response->send_created($row);
    }

    // ----------------------------------------------------------------
    // PUT /users/{id}  +  PATCH /users/{id}
    // ----------------------------------------------------------------

    #[Route('/{id}', methods: ['PUT', 'PATCH'])]
    public function update($id)
    {
        $id     = (int) $id;
        $db     = $this->read_db();
        $row    = $db['rows'][$id] ?? null;

        if ($row === null) {
            $this->response->send_json_error("User $id not found", 404);
            return;
        }

        $method = $this->request->method(true); // 'PUT' or 'PATCH'
        $body   = $this->request->json();
        $errors = [];

        if (empty($body)) {
            $this->response->send_json_error('Request body must not be empty.', 422);
            return;
        }

        // PUT requires every writable field; PATCH allows a subset.
        if ($method === 'PUT') {
            if (!isset($body['name']))  $errors['name']  = 'The name field is required for a full update.';
            if (!isset($body['email'])) $errors['email'] = 'The email field is required for a full update.';
            if (!isset($body['role']))  $errors['role']  = 'The role field is required for a full update.';
        }

        if (isset($body['email'])) {
            if (!filter_var($body['email'], FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'The email must be a valid email address.';
            } else {
                foreach ($db['rows'] as $uid => $existing) {
                    if ((int) $uid !== $id && strtolower($existing['email']) === strtolower($body['email'])) {
                        $errors['email'] = 'This email is already taken.';
                        break;
                    }
                }
            }
        }

        if (!empty($errors)) {
            $this->response->send_json_validation($errors);
            return;
        }

        // Apply only the fields that were sent.
        foreach (['name', 'email', 'role'] as $field) {
            if (isset($body[$field])) {
                $row[$field] = is_string($body[$field]) ? trim($body[$field]) : $body[$field];
            }
        }

        $db['rows'][$id] = $row;
        $this->write_db($db);

        $this->response->send_json_success($row, 'User updated successfully');
    }

    // ----------------------------------------------------------------
    // DELETE /users/{id}
    // ----------------------------------------------------------------

    #[Delete('/{id}')]
    public function destroy($id)
    {
        $id = (int) $id;
        $db = $this->read_db();

        if (!isset($db['rows'][$id])) {
            $this->response->send_json_error("User $id not found", 404);
            return;
        }

        $deleted = $db['rows'][$id];
        unset($db['rows'][$id]);
        $this->write_db($db);

        $this->response->send_json_success($deleted, "User $id deleted successfully");
    }
}