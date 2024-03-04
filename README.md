<p align="center">
    <a href="https://sylius.com" target="_blank">
        <img src="https://demo.sylius.com/assets/shop/img/logo.png" />
    </a>
</p>

<h1 align="center">SyliusUnleashedOrdersPlugin</h1>

## Quickstart Installation

### Traditional

1. Run the following command to install the plugin via Composer:

    ```bash
    composer require forgelabsuk/sylius-unleashed-orders-plugin
    ```

2. Add the Sylius application's routing by creating the file `config/routes/unleashed_orders_plugin.yaml` with the following content:

   ```yaml
   forge_labs_uk_unleashed_orders_shop:
       resource: "@ForgeLabsUkSyliusUnleashedOrdersPlugin/Resources/config/shop_routing.yml"
       prefix: /{_locale}
       requirements:
           _locale: ^[a-z]{2}(?:_[A-Z]{2})?$

   forge_labs_uk_sylius_unleashed_orders_admin:
       resource: "@ForgeLabsUkSyliusUnleashedOrdersPlugin/Resources/config/admin_routing.yml"
       prefix: /%sylius_admin.path_name%


3. Update the code in `src/Entity/Order/Orders.php` (if this file already exists in your Sylius application):

    ```php
    <?php

    declare(strict_types=1);
    
    namespace App\Entity\Order;
    
    use Doctrine\ORM\Mapping as ORM;
    use Sylius\Component\Core\Model\Order as BaseOrder;
    use ForgeLabsUk\SyliusUnleashedOrdersPlugin\Trait\OrderTrait;
    
    /**
     * @ORM\Entity
     * @ORM\Table(name="sylius_order")
     */
    #[ORM\Entity]
    #[ORM\Table(name: 'sylius_order')]
    class Order extends BaseOrder
    {
        use OrderTrait;
    }

    ```

   Ensure you add the `use ForgeLabsUk\SyliusUnleashedOrdersPlugin\Trait\OrderTrait;` statement to import the trait and use it inside the `Product` class.

4. Update the code in `config/packages/_sylius.yaml` (if this file already exists in your Sylius application):

   Add or modify the following configuration under `sylius_order`:

    ```yaml
    sylius_order:
        resources:
            order:
                classes:
                    model: App\Entity\Order\Order
                    controller: ForgeLabsUk\SyliusUnleashedOrdersPlugin\Controller\OrderController
    ```

   Ensure to replace `ForgeLabsUk\SyliusUnleashedOrdersPlugin\Controller\OrderController` with the correct namespace of your order controller.


5. Execute the Doctrine migrations diff to create a migration file:

    ```bash
    php bin/console doctrine:migrations:diff
    ```

6. Execute the Doctrine migrations to set up the database:

    ```bash
    php bin/console doctrine:migrations:migrate
    ```
   
