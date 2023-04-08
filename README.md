# Allyson/Arch-Laravel
Allyson Arch-Laravel é um pacote PHP que fornece uma maneira simples e elegante de organizar a arquitetura do seu aplicativo Laravel. Tem como objetivo promover a modularidade e escalabilidade, fornecendo um conjunto de ferramentas e convenções que facilitam a estruturação da base de código do seu aplicativo em módulos independentes.

## Installation
Você pode instalar o pacote via `composer`:
```bash
composer require allyson/arch-laravel
```

## Como usar

### Exemplo de uso do BaseRequest

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
A classe abstrata BaseRequest fornece os métodos: `indexRules()`, `updateRules()` e  `destroyRules()`, eles retornam um array com regras que permitem o funcionamento adequado do `BaseService` para os métodos `index()`, `update()`, `destroy()`, respectivamente. Podendo ser concatenados com suas próprias regras de negócios. Também é necessário implementar os métodos `hasGroupPermission()` e `isOwner()`, que podem ser implementados de acordo com as necessidades do aplicativo e regras de negócio para autorizar o acesso e uso do recurso.

**File: `./Example/IndexRequest.php`**
```php 
<?php

namespace App\Http\Requests\Example;

class IndexRequest extends ExampleRequest
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
        return $this->indexRules();
    }
}
```
O método `indexRules()` permite navegação através de paginação, definindo o número de itens por página e estabelecendo os critérios de ordenação e filtragem para a consulta.

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
**Detalhes:**
- `page` ***(integer)***: Este parâmetro é usado para especificar o número da página dos resultados a serem recuperados. Deve ser um valor inteiro.
- `perPage` ***(integer)***: Este parâmetro é usado para especificar o número de itens por página a serem recuperados. Deve ser um valor inteiro.
- `orderBy` ***(array)***: Este parâmetro é usado para especificar os critérios de ordenação para a consulta. Deve ser uma matriz contendo um ou mais critérios de ordenação. Cada critério de ordenação é uma matriz contendo dois elementos: a coluna pela qual ordenar e a direção da ordenação (ascendente ou descendente). O nome da coluna é validado para garantir que existe na tabela especificada pela variável `$table`, e a direção da ordenação é validada para garantir que seja *'asc'* ou *'desc'*.
- `wheres` ***(array)***: Este parâmetro é usado para especificar os critérios de filtragem para a consulta. Deve ser uma matriz contendo um ou mais critérios de filtragem. Cada critério de filtragem é uma matriz contendo três elementos: a coluna pela qual filtrar, a condição de filtragem (por exemplo, '=', '<', '>=', 'like', etc.) e o valor a ser procurado. O nome da coluna é validado para garantir que existe na tabela especificada pela variável `$table`, e a condição de filtragem é validada para garantir que seja uma das condições permitidas especificadas pelos `$conditionsOperators`.
- `wheres.*.column` ***(string)***: Este parâmetro é o nome da coluna pela qual filtrar. É um valor de **string obrigatório** que deve existir no array `$searchable` do modelo e deve existir na tabela especificada pela variável `$table`.
- `wheres.*.condition` ***(string)***: Este parâmetro é a condição de filtragem a ser usada para a coluna especificada. É um valor de **string obrigatório** que deve ser uma das condições permitidas especificadas pela constante `$conditionsOperators`.
- `wheres.*.search` ***(string)***: Este parâmetro é o valor a ser procurado na coluna especificada. É um valor de **string obrigatório**.
- orWheres ***(array)***: Este parâmetro é usado para especificar critérios de filtragem adicionais para a consulta. Funciona da mesma forma que o parâmetro wheres, mas as condições de filtragem são combinadas usando o `operador OR` em vez do `operador AND`.
- `orWheres.*.column` ***(string)***: Este parâmetro é o nome da coluna pela qual filtrar. É um valor de **string obrigatório** que deve existir no array `$searchable` do modelo e deve existir na tabela especificada pela variável `$table`.
- `orWheres.*.condition` ***(string)***: Este parâmetro é a condição de filtragem a ser usada para a coluna especificada. É um valor de **string obrigatório** que deve ser uma das condições permitidas especificadas pela constante `$conditionsOperators`.
- `orWheres.*.search` ***(string)***: Este parâmetro é o valor a ser procurado na coluna especificada. É um valor de **string obrigatório**.
```php
public array $conditionsOperators = ['=', '!=', '<>', '<', '>', '<=', '>=', 'LIKE', 'NOT LIKE', 'IS NULL', 'IS NOT NULL'];
```

**File: `./Example/DestroyRequest.php`**
```php 
<?php

namespace App\Http\Requests\Example;

class DestroyRequest extends ExampleRequest
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
        return $this->destroyRules();
    }
}
```
O método `destroyRules()` tem como propósito possibilitar que o BaseService elimine um recurso de forma permanente, juntamente com seus registros dependentes, ou realize uma exclusão suave. Ao aplicar o método `destroyRules()`, basta fornecer o parâmetro `force: true` na solicitação para que a estrutura remova o recurso de modo definitivo, junto aos seus vínculos, de maneira recursiva. Caso o parâmetro `force` não seja fornecido na requisição ou seja igual a `false`, o comportamento padrão do método `delete()` será remover o recurso de forma permanente apenas se não estiver em uso. Se estiver em uso, será executada uma exclusão suave. Tanto a exclusão permanente *(hard delete)* quanto a suave *(soft delete)* ocorrem de forma recursiva.

**JSON Request Example**
```json
{
    "force": true
}
```

**File: `./Example/UpdateRequest.php`**
```php 
<?php

namespace App\Http\Requests\Example;

class UpdateRequest extends ExampleRequest
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
        return [
            ...$this->updateRules()
            'field' => 'bail|required|integer',
            'field2' => 'bail|nullable|string'
        ];
    }
}
```
O método `updateRules()` possui um conjunto de validações que possibilitará que o BaseService restaure o registro, juntamente com seus vínculos. Para isso basta fornecer o parâmetro `$model::DELETED_AT` como nulo na solicitação para que a estrutura reabilite o registro de maneira recursiva.

**JSON Request Example**
```json
{
    "deleted_at": null
}
```

### Exemplo de uso do BaseModel
A classe `BaseModel` é uma classe abstrata que pode ser estendida para criar modelos na sua aplicação Laravel. Abaixo está um exemplo de como usar a classe `BaseModel` para criar um modelo `Paises`.
```php
<?php

namespace App\Models;

use ArchCrudLaravel\App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\{
    HasMany
};

class Paises extends BaseModel
{
    public $table = 'paises';
    protected $fillable = [
        'nome',
        self::DELETED_AT
    ];

    public array $searchable = [
        'nome',
        self::DELETED_AT
    ];

    public function estados(): HasMany
    {
        return $this->hasMany(Estados::class, 'paisId');
    }
}

```

No exemplo acima, a propriedade `$searchable` permite que você especifique quais campos podem ser pesquisados ​​ao usar o método `index()` da classe `BaseService`. Isso é útil ao implementar a funcionalidade de pesquisa na sua aplicação.

Além disso, a classe `BaseModel` fornece as constantes `DELETED_AT`, `UPDATED_AT` e `CREATED_AT` para permitir fácil referenciamento dos timestamps padrão do Laravel. No entanto, você também pode sobrescrever essas constantes fornecendo uma string contendo o nome da coluna na tabela.

Para o correto funcionamento do método `$service->destroy()`, os relacionamentos **DEVEM** ser tipados.

### BaseCollection Usage Example

### BaseResource Usage Example

### Exemplo de Uso do BaseService
O `BaseService` é uma classe fornecida pelo Arch-Laravel que pode ser usada para simplificar a criação de serviços no Laravel. Aqui está um exemplo de como utilizá-lo:
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

Neste exemplo, criamos um novo serviço chamado `ExampleService` que estende a classe `BaseService`.

**Propriedades**

- `$nameModel`: O nome da classe do modelo que o serviço utiliza, se o seu serviço não usar um modelo, não é necessário informar essa propriedade.
- `$nameCollection`: O nome da classe de recurso de coleção que o serviço retorna quando várias instâncias do modelo são solicitadas, o uso desta propriedade é opcional.
- `$nameResource`: O nome da classe de recurso do item que o serviço retorna quando uma única instância do modelo é solicitada, o uso desta propriedade é opcional.
- `$onTransaction`: Controla se os rollbacks de banco de dados serão realizados se ocorrer uma exceção. O ***valor padrão é `true`***.
- `$onCache`: Controla se os resultados dos métodos `show()` e `index()` serão armazenados em cache. O método `update()` cria e atualiza os valores do cache. O ***valor padrão é `true`***.
- `$relationships`: Você pode inserir um array com os relacionamentos que deseja exibir. É possível usar o método `getRelationshipNames()` para obter todos os relacionamentos. O ***padrão é `[]` (um array vazio)***.
- `$ignoreRelationships`: Você pode inserir um array com os nomes dos relacionamentos que deseja ignorar no método `hardDelete()`. É possível usar o método `getRelationshipNames()` para obter todos os relacionamentos. O ***padrão é `[]` (um array vazio)***.
- `$ignoreTypesOfRelationships`: Você pode inserir um array com os tipos de relacionamentos que deseja ignorar no método `hardDelete()`. O ***padrão é `[]` (um array vazio)***.

Todas essas propriedades podem ser definidas no método `__constructor()` ou nos métodos CRUD, da forma que fizer mais sentido em sua aplicação.

**Metódos**

O `BaseService` conta com os métodos que podem ser empregados para adaptar o fluxo de processos conforme as exigências dos seus aplicativos:

- `beforeInsert()`: executado antes da inserção de dados.
- `afterInsert()`: executado após a inserção de dados.
- `beforeList()`: executado antes da listagem de dados.
- `afterList()`: executado após a listagem de dados.
- `beforeSelect()`: executado antes da seleção de um registro.
- `afterSelect()`: executado após a seleção de um registro.
- `beforeModify()`: executado antes da modificação de um registro.
- `afterModify()`: executado após a modificação de um registro.
- `beforeDelete()`: executado antes da exclusão de um registro.
- `afterDelete()`: executado após a exclusão de um registro.

**Vantagens**

A utilização da classe `BaseService` traz consigo inúmeras vantagens, como:

- **Operações CRUD padrão**: O `BaseService` oferece um conjunto padrão de operações CRUD (create, read, update e delete), facilitando a implementação destas funcionalidades nos serviços derivados.
- **Respostas consistentes**: A classe fornece um conjunto padrão de códigos de resposta, garantindo respostas consistentes e padronizadas em toda a aplicação.
- **Gerenciamento de relacionamentos**: Quando a propriedade `$nameModel` é informada, o método `destroy()` do `BaseService` verifica se o recurso está sendo utilizado por outras entidades no banco de dados, utilizando os relacionamentos declarados na model. Um *soft delete* será executado por padrão caso existam vínculos, mas é possível forçar a remoção permanente do recurso, juntamente com a desanexação das entidades relacionadas, utilizando o parâmetro `force: true`.
- **Recuperação de soft deletes**: É possível reverter os *soft deletes* ao atualizar o campo `$model::DELETED_AT` do registro, sendo a restauração executada de maneira recursiva.

Ao estender a classe `BaseService`, você garante uma base sólida e consistente para os serviços de sua aplicação, agilizando o desenvolvimento e facilitando a manutenção.

### Controller Usage Example
Aqui está um exemplo de como usar o `BaseController` fornecido pelo Arch-Laravel em conjunto com o `ExampleService` criado anteriormente:
```php
<?php

namespace App\Http\Controllers\Api;

use ArchCrudLaravel\App\Http\Controllers\BaseController;
use App\Http\Requests\Example\{
    DeleteRequest,
    indexRules,
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

Neste exemplo, criamos um novo controlador chamado `ExampleController`, que estende a classe `BaseController`. Também definimos uma propriedade `$nameService` para indicar qual serviço este controlador utiliza. O serviço `ExampleService` é usado para lidar com as operações CRUD subjacentes e retorna a resposta correta com base no resultado da operação.

Cada método no controlador corresponde a uma ação padrão CRUD. O método `store()` lida com a criação de um novo recurso, o método `index()` lida com a listagem de recursos, o método `show()` lida com a exibição de um recurso específico, o método `update()` lida com a atualização de um recurso existente e o método `destroy()` lida com a exclusão de um recurso existente, eles retornam uma instância da classe `Response` do Laravel, que representa a resposta HTTP retornada pelo controlador.

Todos os métodos do CRUD recebem uma instância de uma classe de ***Request*** (por exemplo, `StoreRequest` ou `UpdateRequest`), que é usada para validar e recuperar os dados enviados pelo cliente. O método `$request->validated()` é **DEVE** sempre ser usado para recuperar os dados validados da solicitação.

## Credits
- [Allyson Pereira](https://github.com/allysonpdm)
- [All Contributors](https://packagist.org/packages/allyson/arch-laravel)

## License
The MIT License (MIT). Please see [License File](https://github.com/allysonpdm/arch-crud-laravel/blob/main/LICENSE.md) for more information.
