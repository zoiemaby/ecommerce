<?php
require_once '../settings/db_class.php';


class User extends db_connection
{
    private ?int   $user_id        = null;
    private ?string $name          = null;  
    private ?string $email         = null;  
    private ?string $password_hash = null; 
    private ?string $country       = null;  
    private ?string $city          = null;  
    private ?string $phone_number  = null;  
    private ?string $image         = null;  
    private int     $role          = 2;     
    private ?string $date_created  = null;  

    private string $table = 'customer';

    public function __construct(?int $user_id = null)
    {
        parent::__construct(); 
        if ($user_id !== null) {
            $this->user_id = $user_id;
            $this->loadUser();
        }
    }


    public function loadUser(?int $user_id = null): bool
    {
        if ($user_id !== null) {
            $this->user_id = $user_id;
        }
        if ($this->user_id === null) return false;

        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE customer_id = ?");
        $stmt->execute([$this->user_id]);
        $row = $stmt->fetch();
        if (!$row) return false;

        $this->name          = $row['customer_name'];
        $this->email         = $row['customer_email'];
        $this->password_hash = $row['customer_pass'];
        $this->country       = $row['customer_country'] ?? null;
        $this->city          = $row['customer_city'] ?? null;
        $this->phone_number  = $row['customer_contact'] ?? null;
        $this->image         = $row['customer_image'] ?? null;
        $this->role          = (int)$row['user_role'];
        $this->date_created  = $row['date_created'] ?? null; 
        return true;
    }


    public function authenticate(string $email, string $password): ?array {
        $stmt = $this->db->prepare("SELECT * FROM customer WHERE customer_email = ? LIMIT 1");
        $stmt->execute([strtolower(trim($email))]);
        $row = $stmt->fetch();
        if ($row && password_verify($password, $row['customer_pass'])) {
            return $row;
        }
        return null;
    }


    public function createUser(
        string $name,
        string $email,
        string $password,
        string $phone_number,
        int    $role = 2,
        ?string $country = null,
        ?string $city = null,
        ?string $image = null
    ) {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO {$this->table}
                (customer_name, customer_email, customer_pass, customer_country, customer_city, customer_contact, customer_image, user_role)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $ok = $stmt->execute([
            $name, $email, $hash, $country, $city, $phone_number, $image, $role
        ]);

        if ($ok) {
            $this->user_id       = (int)$this->db->lastInsertId();
            $this->name          = $name;
            $this->email         = $email;
            $this->password_hash = $hash;
            $this->country       = $country;
            $this->city          = $city;
            $this->phone_number  = $phone_number;
            $this->image         = $image;
            $this->role          = $role;
            return $this->user_id;
        }
        return false;
    }

    public function add(array $a): bool
    {
        $id = $this->createUser(
            $a['customer_name'],
            $a['customer_email'],
            $a['customer_pass_plain'] ?? '', 
            $a['customer_contact'],
            (int)($a['user_role'] ?? 2),
            $a['customer_country'] ?? null,
            $a['customer_city'] ?? null,
            $a['customer_image'] ?? null
        );

        if (!$id && isset($a['customer_pass'])) {
            $sql = "INSERT INTO {$this->table}
                    (customer_name, customer_email, customer_pass, customer_country, customer_city, customer_contact, customer_image, user_role)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $a['customer_name'], $a['customer_email'], $a['customer_pass'],
                $a['customer_country'] ?? null, $a['customer_city'] ?? null,
                $a['customer_contact'], $a['customer_image'] ?? null, (int)($a['user_role'] ?? 2)
            ]);
        }
        return (bool)$id;
    }

    /** Check if an email already exists */
    public function emailExists(string $email): bool
    {
        $stmt = $this->db->prepare("SELECT 1 FROM {$this->table} WHERE customer_email = ? LIMIT 1");
        $stmt->execute([$email]);
        return (bool)$stmt->fetchColumn();
    }


    public function getUserByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE customer_email = ? LIMIT 1");
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function id(): ?int { return $this->user_id; }
    public function name(): ?string { return $this->name; }
    public function email(): ?string { return $this->email; }
    public function role(): int { return $this->role; }
    public function phone(): ?string { return $this->phone_number; }
    public function country(): ?string { return $this->country; }
    public function city(): ?string { return $this->city; }
    public function image(): ?string { return $this->image; }

    /** Export the in-memory user as an array */
    public function toArray(): array
    {
        return [
            'customer_id'     => $this->user_id,
            'customer_name'   => $this->name,
            'customer_email'  => $this->email,
            'customer_country'=> $this->country,
            'customer_city'   => $this->city,
            'customer_contact'=> $this->phone_number,
            'customer_image'  => $this->image,
            'user_role'       => $this->role,
            'date_created'    => $this->date_created
        ];
    }
}