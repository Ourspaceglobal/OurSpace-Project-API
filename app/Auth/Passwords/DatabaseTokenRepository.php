<?php

namespace App\Auth\Passwords;

use Illuminate\Auth\Passwords\DatabaseTokenRepository as PasswordsDatabaseTokenRepository;
use Illuminate\Contracts\Auth\CanResetPassword;

class DatabaseTokenRepository extends PasswordsDatabaseTokenRepository
{
    /**
     * Create a new token record.
     *
     * @param \Illuminate\Contracts\Auth\CanResetPassword $user
     * @return string
     */
    public function create(CanResetPassword $user)
    {
        $email = $user->getEmailForPasswordReset();

        $this->deleteExisting($user);

        // We will create a new, random token for the user so that we can e-mail them
        // a safe link to the password reset form. Then we will insert a record in
        // the database so that we can verify the token within the actual reset.
        $token = $this->createNewToken();

        // Generate a secure code
        // Remember that the length of this value is essential for the validation in $this->exists()
        $code = mt_rand(100000, 999999);

        $this->getTable()->insert($this->getNewPayload($email, $token, $code));

        return $token;
    }

    /**
     * Build the record payload for the table.
     *
     * @param string $email
     * @param string $token
     * @param string $code
     * @return array
     */
    protected function getNewPayload($email, $token, $code)
    {
        return [
            'email' => $email,
            'token' => $this->hasher->make($token),
            'code' => $code,
            'created_at' => now(),
        ];
    }

    /**
     * Determine if a token record exists and is valid.
     *
     * @param \Illuminate\Contracts\Auth\CanResetPassword $user
     * @param string $token
     * @return bool
     */
    public function exists(CanResetPassword $user, $token)
    {
        $record = (array) $this->getTable()
            ->where('email', $user->getEmailForPasswordReset())
            ->first();

        return $record &&
            !$this->tokenExpired($record['created_at']) &&
                strlen($token) === 6
                    ? $token === $record['code']
                    : $this->hasher->check($token, $record['token']);
    }

    /**
     * Getter for used table.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function getUsedTable()
    {
        return $this->getTable();
    }
}
