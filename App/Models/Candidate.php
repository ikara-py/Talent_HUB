<?php

namespace App\Models;

class Candidate extends User
{
    
    private $phone;
    private $tags; 
    private $created_at;

    public function __construct(
        
        string $phone ,
        array $tags,
        string $created_at 
    ) {
        
        $this->phone = $phone;
        $this->tags = $tags;
        $this->created_at = $created_at;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function gettags(): array
    {
        return $this->tags;
    }

    public function getCreatedAt()
    {
        return $this->created_at;
    }

    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    public function settags(array $tags)
    {
        $this->tags = $tags;
    }

    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
    }
}

?>