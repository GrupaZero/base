<?php namespace Gzero\Base\Jobs;

use Gzero\Base\Models\User;
use Illuminate\Support\Facades\DB;

class CreateUser {

    /** @var string */
    protected $email;

    /** @var string */
    protected $password;

    /** @var string */
    protected $name;

    /** @var string */
    protected $firstName;

    /** @var string */
    protected $lastName;

    /**
     * Create a new job instance.
     *
     * @param string $email     Email
     * @param string $password  Password
     * @param string $name      Name
     * @param string $firstName First name
     * @param string $lastName  Last name
     */
    public function __construct(
        string $email,
        string $password,
        ?string $name = null,
        ?string $firstName = null,
        ?string $lastName = null
    ) {
        $this->email     = $email;
        $this->password  = $password;
        $this->name      = $name;
        $this->firstName = $firstName;
        $this->lastName  = $lastName;
    }

    /**
     * Execute the job.
     *
     * @return User
     */
    public function handle()
    {
        if (empty($this->name)) { // handle empty nickname users
            $this->name = $this->buildUniqueNickname();
        }
        $user = DB::transaction(
            function () {
                $user = new User();
                $user->fill([
                    'email'      => $this->email,
                    'password'   => $this->password,
                    'name'       => $this->name,
                    'first_name' => $this->firstName ?: null,
                    'last_name'  => $this->lastName ?: null,
                ]);
                $user->save();
                return $user;
            }
        );
        event('user.created', [$user]);
        return $user;
    }

    /**
     * Function returns an unique user nickname from given url in specific language
     *
     * @param string $replacement string nick replacement to use, "Anonymous" is default
     *
     * @return string $nickname an unique user nickname
     */
    protected function buildUniqueNickname($replacement = 'anonymous')
    {
        return $replacement . '-' . uniqid(User::max('id'));
    }
}
