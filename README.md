# Allyson/Arch-Laravel
Allyson Arch-Laravel is a PHP package that provides a simple and elegant way to organize your Laravel application architecture. It aims to promote modularity and scalability by providing a set of tools and conventions that make it easy to structure your application codebase into independent modules.

## Installation
You can install the package via `composer`:
```bash
composer require allyson/arch-laravel
```
## BaseRequest Usage Example

**File: `./Example/ExampleRequest.php`**
```php 
<?php

namespace App\Http\Requests\Example;

use ArchCrudLaravel\App\Http\Requests\BaseRequest;
use App\Models\Example;

abstract class ExampleRequest extends BaseRequest
{
    protected $model = Example::class;

    protected function hasGroupPermission(): bool
    {
        // Code...
    }

    protected function isOwner(string $method): bool
    {
        // Code...
    }
}
```
The abstract class `BaseRequest` provides two methods, `indexRequest()` and `destroyRequest()`, both of which return rules that allow the proper functioning of the `BaseService` for the `index()` and `destroy()` methods respectively. It also requires the implementation of the `hasGroupPermission()` and `isOwner()` methods, which can be implemented according to the app's needs and business rules to authorize access and use of the resource.

**File: `./Example/IndexRequest.php`**
```php 
<?php

namespace App\Http\Requests\Example;

class IndexRequest extends UsersRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->hasGroupPermission();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return $this->indexRequest();
    }
}
```
The `indexRequest()` method allows navigation through pagination, setting the number of items per page, and defining the sorting and filtering criteria for the query.

- `page` (integer): This parameter is used to specify the page number of the results to retrieve. It must be an integer value.
- `perPage` (integer): This parameter is used to specify the number of items per page to retrieve. It must be an integer value.
- `orderBy` (array): This parameter is used to specify the sorting criteria for the query. It must be an array containing one or more sorting criteria. Each sorting criterion is an array containing two elements: the column to sort by and the sorting direction (either 'asc' or 'desc'). The column name is validated to ensure that it exists in the table specified by the `$table` - `variable`, and the sorting direction is validated to ensure that it is either 'asc' or 'desc'.
- `wheres` (array): This parameter is used to specify the filtering criteria for the query. It must be an array containing one or more filtering criteria. Each filtering criterion is an array containing three elements: the column to filter by, the filtering condition (e.g. '=', '<', '>=', 'like', etc.), and the value to search for. The column name is validated to ensure that it exists in the table specified by the $table variable, and the filtering condition is validated to ensure that it is one of the allowed conditions specified by the `self::CONDITIONS_OPERATORS` constant.
- `wheres.*.column` (string): This parameter is the name of the column to filter by. It is a required string value that must exist in the `$searchable` array of the model, and it must exist in the table specified by the `$table` variable.
- `wheres.*.condition` (string): This parameter is the filtering condition to use for the specified column. It is a required string value that must be one of the allowed conditions specified by the `self::CONDITIONS_OPERATORS` constant.
- `wheres.*.search` (string): This parameter is the value to search for in the specified column. It is a required string value.
- `orWheres` (array): This parameter is used to specify additional filtering criteria for the query. It works the same way as the wheres parameter, but the filtering conditions are combined using the `OR operator` instead of the `AND operator`.
- `orWheres.*.column` (string): This parameter is the name of the column to filter by. It is a required string value that must exist in the `$searchable` array of the model, and it must exist in the table specified by the `$table` variable.
- `orWheres.*.condition` (string): This parameter is the filtering condition to use for the specified column. It is a required string value that must be one of the allowed conditions specified by the `self::CONDITIONS_OPERATORS` constant.
- `orWheres.*.search` (string): This parameter is the value to search for in the specified column. It is a required string value.

**JSON Request Example**
```json
{
    "perPage": 15,
    "page": 1,
    "orderBy": {
        "id": "asc"
    },
    "wheres": [
        {
            "column": "name",
            "condition": "like",
            "search": "%zeck%"
        },
        {
            "column": "years",
            "condition": ">",
            "search": "18"
        }
    ],
    "orWheres": [
        {
            "column": "id",
            "condition": "=",
            "search": "50"
        }
    ]
}
```


**File: `./Example/DestroyRequest.php`**
```php 
<?php

namespace App\Http\Requests\Example;

class DestroyRequest extends UsersRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->hasGroupPermission();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return $this->destroyRequest();
    }
}
```
The `destroyRequest()` method is intended to allow the `BaseService` to permanently remove the resource, along with its childrens records, or perform a soft delete. When the `destroyRequest()` method is applied, simply pass the `force: true` parameter in the request to have the architecture remove the resource permanently along with its child records. If `force: false` or the `destroyRequest()` method is not used, the default behavior for `delete()` will attempt to remove the resource permanently if it is not in use. Otherwise, a soft delete will be performed.

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
- `$nameModel`: The name of the model class that the service uses, if your service does not use a model it is not necessary to inform this property.
- `$nameCollection`: The name of the collection resource class that the service returns when multiple instances of the model are requested, the use of this property is optional.
- `$nameResource`: The name of the item resource class that the service returns when a single instance of the model is requested, the use of this property is optional.

Others properties to configure the service:
- `$onTransaction`: Controls whether database rollbacks will be performed if an exception occurs. The ***value default is `true`***.
- `$onCache`: Controls whether the results of the `show()` and `index()` methods will be cached. The `update()` method creates and updates cache values. The ***value default is `true`***.
- `$relationships`: You can enter an array with the relationships you want to display. It is possible to use the `getRelationships()` method to get all the relationships. The ***default is `[]` (an empty array)***.

All these properties can be defined in the `__constructor()` method or in the CRUD methods, in the way that makes the most sense in your application.

By extending the `BaseService` class, we get several benefits, including:
- The `BaseService` provides a set of default CRUD operations (create, read, update, and delete) that can be used by the service.
- The `BaseService` handles common validation tasks automatically, such as checking if a requested resource exists or if a unique constraint is violated.
- The `BaseService` provides a set of default response codes that can be returned by the service, making it easy to create consistent responses across the application.
- It's worth noting that when the `$nameModel` property is informed, the `destroy()` method of `BaseService` will check if the resource is being used by another entity in the database. If so, it will perform a soft delete by default. However, it is possible to provide the `force: true` parameter in the request, and this will permanently remove the resource and also perform any necessary detachments related to the removed resource.

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
- [Allyson Pereira](https://github.com/allysonpdm)
- [All Contributors](https://packagist.org/packages/allyson/arch-laravel)

## License
The MIT License (MIT). Please see [License File](https://github.com/allysonpdm/arch-crud-laravel/blob/main/LICENSE.md) for more information.
