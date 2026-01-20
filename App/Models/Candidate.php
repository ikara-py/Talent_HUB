<?php

namespace App\Models;

class Candidate extends User
{
    
    private $phone;
    private $skills; 
    private $created_at;

    public function __construct(
        
        string $phone ,
        array $skills,
        string $created_at 
    ) {
        
        $this->phone = $phone;
        $this->skills = $skills;
        $this->created_at = $created_at;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function getSkills()
    {
        return $this->skills;
    }

    public function getCreatedAt()
    {
        return $this->created_at;
    }

    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    public function setSkills($skills)
    {
        $this->skills = $skills;
    }

    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
    }
}

?>