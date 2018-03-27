<?php
/**
 * Created by PhpStorm.
 * User: sammmium
 * Date: 27.03.18
 * Time: 1:19
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\User as BaseUser;

/**
 * @ORM\Entity
 * @ORM\Table(name="`user`")
 */
class Executor extends BaseUser
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected $id;

    public function __construct()
    {
        parent::__construct();
        // your own logic
    }
}