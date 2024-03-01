<?php
namespace ForgeLabsUk\SyliusUnleashedOrdersPlugin\Trait;

trait OrderTrait
{
    /**
     * @var string
     * @ORM\Column(type="guid",nullable=true)
     */
    protected $guid;

    /**
     * @var string
     * @ORM\Column(type="string",nullable=true)
     */
    protected $unleashedStatus;

    // Add getter and setter methods
    public function getGuid(): ?string
    {
        return $this->guid;
    }

    public function setGuid(?string $guid): void
    {
        $this->guid = $guid;
    }

    public function getUnleashedStatus(): ?string
    {
        return $this->unleashedStatus;
    }

    public function setUnleashedStatus(?string $status): void
    {
        $this->unleashedStatus = $status;
    }
}
