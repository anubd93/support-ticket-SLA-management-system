<?php

namespace App\Services\V1;

use App\Enums\V1\UserRole;
use App\Models\User;
use App\Repositories\V1\CategoryRepositoryV1;
use App\Repositories\V1\RoleRepositoryV1;
use App\Repositories\V1\UserRepositoryV1;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class UserServiceV1
{
    public function __construct(private readonly UserRepositoryV1 $userRepositoryV1,
                                private readonly RoleRepositoryV1 $roleRepositoryV1,
                                private readonly CategoryRepositoryV1 $categoryRepositoryV1,
                                private readonly TicketAssignmentServiceV1 $ticketAssignmentServiceV1
    ) {}

    public function getUsers(array $filters): LengthAwarePaginator
    {
        $users = $this->userRepositoryV1->setFilters($filters)->paginateUsers(config('tickets.per_page'));
        $skills = $this->userRepositoryV1->getCategoryNamesByUserIds($users->pluck('id')->all());

        $users->getCollection()->transform(function ($user) use ($skills) {
            $user->categories = $skills[$user->id] ?? [];

            return $user;
        });

        return $users;
    }

    public function findUser(int $id): ?object
    {
        $user = $this->userRepositoryV1->setId($id)->findById();

        if ($user) {
            $user->category_ids = $this->userRepositoryV1->getCategoryIds();
        }

        return $user;
    }

    public function getFormOptions(): array
    {
        return [
            'roles' => $this->roleRepositoryV1->getAll(),
            'categories' => $this->categoryRepositoryV1->getActive(),
        ];
    }

    public function create(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $isAgent = $this->isAgentRole((int) $data['role_id']);

            $user = $this->userRepositoryV1
                ->setRoleId($data['role_id'])
                ->setName($data['name'])
                ->setEmail($data['email'])
                ->setPassword($data['password'])
                ->store();
                
            $this->userRepositoryV1->syncCategories($user, $isAgent ? ($data['category_ids'] ?? []) : []);

            return $user;
        });
    }

    public function update(int $id, array $data, User $actor): User
    {
        return DB::transaction(function () use ($id, $data, $actor) {
            $user = $this->userRepositoryV1->setId($id)->findModel();
            $isSelf = $user->id === $actor->id;
            
            if ($isSelf && (int) $data['role_id'] !== (int) $user->role_id) {
                throw new Exception('You cannot change your own role.');
            }

            $wasAgent = $this->isAgentRole((int) $user->role_id);
            $isAgent = $this->isAgentRole((int) $data['role_id']);

            $this->userRepositoryV1
                ->setRoleId($data['role_id'])
                ->setName($data['name'])
                ->setEmail($data['email'])
                ->setPassword($data['password'] ?? null)
                ->update($user);

            $this->userRepositoryV1->syncCategories($user, $isAgent ? ($data['category_ids'] ?? []) : []);
            
            if ($wasAgent && ! $isAgent) {
                $this->ticketAssignmentServiceV1->unassignAllFromAgent($user->id, $actor->id);
            }

            return $user;
        });
    }

    private function isAgentRole(int $roleId): bool
    {
        return $this->roleRepositoryV1->getAll()->firstWhere('id', $roleId)?->name === UserRole::Agent->value;
    }
}
