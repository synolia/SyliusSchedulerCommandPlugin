# Install plugin

Import required config in your `config/packages/_sylius.yaml` file:

```yaml
# config/packages/_sylius.yaml

imports:
    ...
    
    - { resource: "@SynoliaSyliusSchedulerCommandPlugin/Resources/config/config.yml" }
```

Import routing in your `config/routes.yaml` file:

```yaml

# config/routes.yaml
...

synolia_scheduled_command:
    resource: "@SynoliaSyliusSchedulerCommandPlugin/Resources/config/admin_routing.yml"
    prefix: /admin
```



