<?php
class PremiumUser
{
    private $connection;

    public function __construct($connection)
    {
        $this->connection = $connection;
    }

    public function checkLogin()
    {
        if (isset($_GET['user_id']))
        {
            $_SESSION['user_id'] = $_GET['user_id'];
        }

        if (isset($_SESSION['user_id']))
        {
            $user_id = $_SESSION['user_id'];

            // Use a prepared statement to prevent SQL injection
            $stmt = $this
                ->connection
                ->prepare("SELECT * FROM premium_users WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows > 0)
            {
                $user_data = $result->fetch_assoc();
                return $user_data;
            }
        }
        else
        {
            header("Location: premium_login.php");
            die;
        }
    }

    public function getPremiumUserById($user_id)
    {
        $stmt = $this
            ->connection
            ->prepare("SELECT * FROM premium_users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result;
    }

    public function getPremiumUserByUsername($user_name)
    {
        $stmt = $this
            ->connection
            ->prepare("SELECT * FROM premium_users WHERE user_name = ?");
        $stmt->bind_param("s", $user_name);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0)
        {
            $user_data = $result->fetch_assoc();
            return $user_data;
        }
        return null;
    }

    /*
    The function may not always generate a truly prime number, 
    but it should be sufficient for your needs.
    */
    private function generate_random_prime($bits)
    {
    // Generate a random number of $bits length
    $random_number = gmp_random_bits($bits);

    // Make sure it's odd, to increase the probability that it's prime
    if (gmp_mod($random_number, 2) == 0) {
        $random_number = gmp_add($random_number, 1);
    }

    // Increment by 2 (to stay odd) until we find a prime
    while (!gmp_prob_prime($random_number)) {
        $random_number = gmp_add($random_number, 2);
    }

    return $random_number;
    }


    public function generate_stateful_rsa_keys($bit_length = 1024)
    {
        // Generate a random prime number p
        $p = $this->generate_random_prime(($bit_length - 1) / 2);

        // q is a function of p: SHA-256(p), converted to number, next prime number greater than that
        $hash = hash('sha256', gmp_strval($p));
        $number = gmp_init('0x' . $hash); // convert hex hash to number
        $q = gmp_nextprime($number);
    
        // Calculate n = p * q
        $n = gmp_mul($p, $q);
    
        // Calculate the totient = (p-1) * (q-1)
        $totient = gmp_mul(gmp_sub($p, 1) , gmp_sub($q, 1));
    
        // Choose e such that 1 < e < totient and e and totient are coprime
        $e = gmp_init(3);
        while (gmp_gcd($e, $totient) != 1)
        {
            $e = gmp_add($e, 2);
        }
    
        // Calculate d, the modular multiplicative inverse of e (mod totient)
        $d = gmp_invert($e, $totient);
    
        // Return only the public key (e, n)
        return ['public' => ['e' => gmp_strval($e) , 'n' => gmp_strval($n) ]];
    }
    

    public function generateAndStoreKeysForUser($user_id) {
        $rsa_keys = $this->generate_stateful_rsa_keys();
        $public_key = $rsa_keys['public'];

        // Update the user's keys in the database
        $stmt = $this->connection->prepare("UPDATE premium_users SET public_key_e = ?, public_key_n = ? WHERE user_id = ?");
        $stmt->bind_param("ssi", $public_key['e'], $public_key['n'], $user_id);
        $stmt->execute();
    }
}

?>
