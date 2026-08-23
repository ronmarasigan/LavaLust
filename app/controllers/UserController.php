<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

#[Route('/users')]
class UserController extends Controller {

	#[Get('/')]
	public function index() {
		echo "list users\n";
	}

	#[Get('/{id}')]
	#[Name('users.show')]
	public function show($id) {
		echo "show user $id\n";
	}

	#[Post('/')]
	public function store() {
		echo "create user\n";
	}

	#[Route('/{id}', methods: ['PUT', 'PATCH'])]
	public function update($id) {
		echo "update user $id\n";
	}
}
?>