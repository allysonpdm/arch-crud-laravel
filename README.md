# Allyson/Arch-Laravel
Allyson Arch-Laravel is a PHP package that provides a simple and elegant way to organize your Laravel application architecture. It aims to promote modularity and scalability by providing a set of tools and conventions that make it easy to structure your application codebase into independent modules.

## Installation
You can install the package via `composer`:
```
composer require allyson/arch-laravel
```
## BaseRequest Usage Example
## BaseModel Usage Example
## BaseCollection Usage Example
## BaseResource Usage Example


## BaseService Usage Example
The `BaseService` is a class provided by Arch-Laravel that can be used to simplify the creation of Laravel services. Here's an example of how to use it:

```php
<?php

namespace App\Services\Api;

use App\Http\Resources\Tratamentos\ExampleCollection;
use App\Http\Resources\Tratamentos\ExampleResource;
use App\Models\Tratamentos\Example;
use ArchCrudLaravel\App\Services\BaseService;

class ExampleService extends BaseService
{
    protected $nameModel = Example::class;
    protected $nameCollection = ExampleCollection::class;
    protected $nameResource = ExampleResource::class;
}

```

In this example, we've created a new service called ExampleService that extends the `BaseService` class. We've also defined three properties to configure the service:
- `$nameModel`: The name of the model class that the service uses.
- `$nameCollection`: The name of the collection resource class that the service returns when multiple instances of the model are requested.
- `$nameResource`: The name of the item resource class that the service returns when a single instance of the model is requested.

Others properties to configure the service:
- `$onTransaction`: Controls whether database rollbacks will be performed if an exception occurs, default is true.
- `$onCache`: Controls whether the results of the `show()` and `list()` methods will be cached. The `update()` method creates and updates cache values, the default is true.
- `$relationships`: You can enter an array with the relationships you want to display. It is possible to use the `getRelationships()` method to get all the relationships. The default is an empty array.

All these properties can be defined in the `__constructor()` method or in the CRUD methods, in the way that makes the most sense in your application.

By extending the `BaseService` class, we get several benefits, including:
- The `BaseService` provides a set of default CRUD operations (create, read, update, and delete) that can be used by the service.
- The `BaseService` handles common validation tasks automatically, such as checking if a requested resource exists or if a unique constraint is violated.
- The `BaseService` provides a set of default response codes that can be returned by the service, making it easy to create consistent responses across the application.

## Controller Usage Example
Here's an example of how to use the `BaseController` provided by Arch-Laravel in conjunction with the `ExampleService` created earlier:
```php
<?php

namespace App\Http\Controllers\Api;

use ArchCrudLaravel\App\Http\Controllers\BaseController;
use App\Http\Requests\Example\{
    DeleteRequest,
    IndexRequest,
    StoreRequest,
    ShowRequest,
    UpdateRequest
};
use App\Services\Api\ExampleService;
use Illuminate\Http\Response;

class ExampleController extends BaseController
{
    protected $nameService = ExampleService::class;

    // These methods are optional and correspond to standard CRUD actions
    public function store(StoreRequest $request): Response
    {
        return $this->service->store($request->validated());
    }

    public function index(IndexRequest $request): Response
    {
        return $this->service->index($request->validated());
    }

    public function show(ShowRequest $request, int $id): Response
    {
        return $this->service->show($request->validated(), $id);
    }

    public function update(UpdateRequest $request, int $id): Response
    {
        return $this->service->update($request->validated(), $id);
    }

    public function destroy(DeleteRequest $request, int $id): Response
    {
        return $this->service->destroy($request->validated(), $id);
    }

    // Additional methods for custom actions or endpoints can be defined as needed
    public function customAction(Request $request): Response
    {
        // ...
    }
}

```

In this example, we create a new controller called `ExampleController` which extends the `BaseController` class. We also define a `$nameService` property to indicate which service this controller uses.

Each method in the controller corresponds to a standard CRUD action. The `store()` method handles creating a new resource, the `index()` method handles listing resources, the `show()` method handles displaying a specific resource, the `update()` method handles updating an existing resource, and the `destroy()` method handles deleting an existing resource.

Each method receives an instance of a request class (e.g. `StoreRequest` or `UpdateRequest`) which is used to validate and retrieve the data sent by the client. The `validated()` method is used to retrieve the validated data from the request.

Each method returns an instance of Laravel's `Response` class which represents the HTTP response returned by the controller. The `ExampleService` service is used to handle the underlying CRUD operations and returns the correct response based on the result of the operation.


## Credits
[Allyson Pereira](https://github.com/allysonpdm)

[All Contributors](https://packagist.org/packages/allyson/arch-laravel)

## License
The MIT License (MIT). Please see [License File](https://packagist.org/packages/allyson/arch-laravel) for more information.
