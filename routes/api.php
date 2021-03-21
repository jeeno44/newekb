<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// Регистрация пользователя
// Делаем запрос на адрес
// Нужно выполнить GET запрос по адресу http(s)://domen.com/api/register/<username>/<password>
// где <username> - логин пользователя (ОБЯЗАТЕЛЬНЫЙ АРГУМЕНТ)
// где <password> - пароль пользователя (ОБЯЗАТЕЛЬНЫЙ АРГУМЕНТ)
// Если регистрация прошла успешно то в ответе будет сообщение о успешной регистрации
// После успешной регистрации нужно авторизироваться и получить код авторизации

Route::get('/register/{login}/{password}', function (Request $request,$login,$password) {

    function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    try {
        \App\Models\User::create(["name" => $login,"password" => $password,"auth_code" => generateRandomString(20)]);
        $getUser = \App\Models\User::orderBy("id","desc")->first();
        return "Регистрация успешно завершена";
    }
    catch (\Mockery\Exception $e){
        return $e->getMessage();
    }

});

// Авторизация пользователя
// Сделать запрос по адресу
// Нужно выполнить GET запрос по адресу http(s)://domen.com/api/auth/<username>/<password>
// где <username> - логин пользователя
// где <password> - пароль пользователя
// Если логин и пароль введены верно, то в ответе придёт код авторизии
// Вам нужно сохранить этот код для последующих запросов ()

Route::get('/auth/{login}/{password}', function (Request $request,$login,$password) {

    $user = \App\Models\User::where("name",$login)->where("password", $password)->first();

    if ($user){
        return $user->auth_code;
    }
    else{
        return "Пользователь не найден";
    }
});

// Получить все категории
// Получить категории может только авторизированный пользователь
// Нужно выполнить GET запрос на адрес http(s)://domen.com/api/<auth_code>/categories/all
// где <auth_code> - код авторизации пользователя (ОБЯЗАТЕЛЬНЫЙ АРГУМЕНТ)

Route::get('/{auth_code}/categories/all', function (Request $request,$auth_code) {

    $user = \App\Models\User::where("auth_code",$auth_code)->first();

    if ($user){
        $categories = \App\Models\Categorie::all();

        if(count($categories) > 0){
            return json_encode($categories);
        }
        else{
            return "Отсутствуют категории";
        }
    }
    else{
        return "Пользователь не найден";
    }

});

// Добавление категории
// Получить категории может только авторизированный пользователь
// Нужно выполнить GET запрос на адрес http(s)://domen.com/api/<auth_code>/categories/append/<name>/<?about>
// где <auth_code> - код авторизации пользователя (ОБЯЗАТЕЛЬНЫЙ АРГУМЕНТ)
// где <name> - название категории (ОБЯЗАТЕЛЬНЫЙ АРГУМЕНТ)
// где <?about> - описание категории (НЕОБЯЗАТЕЛЬНЫЙ АРГУМЕНТ)

Route::get('/{auth_code}/categories/append/{name}/{about?}', function (Request $request,$auth_code,$name,$about = null) {

    $user = \App\Models\User::where("auth_code",$auth_code)->first();

    if ($user){
        $findCategorie = \App\Models\Categorie::where("categorie",$name)->first();

        if ($findCategorie == null) {
            \App\Models\Categorie::create(["categorie" => $name,"about" => ($about) ? $about : null]);
            return "Категория добавлена";
        }
        else{
            return "Такая категория уже существует";
        }
    }
    else{
        return "Пользователь не найден";
    }

});

// Редактирование категории
// Редактировать категории может только авторизированный пользователь
// Нужно выполнить GET запрос на адрес http(s)://domen.com/api/<auth_code>/categories/edit/<id>/<name>/<?about>
// где <auth_code> - код авторизации пользователя (ОБЯЗАТЕЛЬНЫЙ АРГУМЕНТ)
// где <id> - идентификатор категории
//                      идентификатор категории можно получить по адресу
//                      http(s)://domen.com/api/<auth_code>/categories/all
// где <name> - имя категории (ОБЯЗАТЕЛЬНЫЙ АРГУМЕНТ)
// где <about> - описание категории (НЕОБЯЗАТЕЛЬНЫЙ АРГУМЕНТ)

Route::get('/{auth_code}/categories/edit/{id}/{name}/{about?}', function (Request $request,$auth_code,$id,$name,$about = null) {

    $user = \App\Models\User::where("auth_code",$auth_code)->first();

    if ($user){
        try {
            $categorie = \App\Models\Categorie::find($id);
            $categorie->categorie = $name;
            $categorie->about = ($about) ? $about : null;
            $categorie->save();

            return "Категория отредактирована успешно";
        }
        catch (\Mockery\Exception $exception){
            return $exception->getMessage();
        }
    }
    else{
        return "Пользователь не найден";
    }

});

// Удаление категории
// Удалять категории может только авторизированный пользователь
// Нужно выполнить GET запрос на адрес http(s)://domen.com/api/<auth_code>/categories/delete/<id>
// где <auth_code> - код авторизации пользователя (ОБЯЗАТЕЛЬНЫЙ АРГУМЕНТ)
// где <id> - идентификатор катеогории (ОБЯЗАТЕЛЬНЫЙ АРГУМЕНТ)

Route::get('/{auth_code}/categories/delete/{id}/', function (Request $request,$auth_code,$id) {

    $user = \App\Models\User::where("auth_code",$auth_code)->first();

    if ($user){

        if (\App\Models\Categorie::where("id",$id)->exists()){
            try {
                \App\Models\CategorieProduct::where("categorie_id",$id)->delete();
                $categorie = \App\Models\Categorie::find($id);
                $name = $categorie->categorie;
                $categorie->delete();
            }
            catch (\Mockery\Exception $exception){
                return $exception->getMessage();
            }
            return "Категория \"$name\" успешно удалена";
        }
        else{
            return "Категория не найдена";
        }

    }
    else{
        return "Пользователь не найден";
    }

});

// Получение продуктов
// Получить продукты может только авторизированный пользователь
// Нужно выполнить GET запрос на адрес http(s)://domen.com/api/<auth_code>/products/all
// где <auth_code> - код авторизации пользователя (ОБЯЗАТЕЛЬНЫЙ АРГУМЕНТ)

Route::get('/{auth_code}/products/all/', function (Request $request,$auth_code) {

    $user = \App\Models\User::where("auth_code",$auth_code)->first();

    if ($user){

        $products = \App\Models\Product::all();

        if(count($products) > 0){
            return json_encode($products);
        }
        else{
            return "Продукты отсутствуют";
        }

    }
    else{
        return "Пользователь не найден";
    }

});

// Получение продуктов конкретной категории
// Получить продукты может только авторизированный пользователь
// Нужно выполнить GET запрос на адрес http(s)://domen.com/api/<auth_code>/products/categorie/<id_categorie>
// где <auth_code> - код авторизации пользователя (ОБЯЗАТЕЛЬНЫЙ АРГУМЕНТ)
// где <id_categorie> - идентификатор категории (ОБЯЗАТЕЛЬНЫЙ АРГУМЕНТ)
//                      получить идентификатор категории можно по адресу http(s)://domen.com/api/<auth_code>/categories/all
// В ответе придёт json список всех продуктов с указанной категорией

Route::get('/{auth_code}/products/categorie/{id_categorie}', function (Request $request,$auth_code,$id_categorie) {

    $user = \App\Models\User::where("auth_code",$auth_code)->first();

    if ($user){

        if (\App\Models\Categorie::where("id",$id_categorie)->exists()){

            $products = \App\Models\Categorie::find($id_categorie)->Products;

            $productsByCategorie = [];

            foreach ($products as $product) {
                $productsByCategorie[] = $product->Product;
            }

            if(count($productsByCategorie) > 0){
                return json_encode($productsByCategorie);
            }
            else{
                return "Продукты у категории отсутствуют";
            }
        }
        else{
            return "Категория не найдена";
        }

    }
    else{
        return "Пользователь не найден";
    }

});

// Добавление продуктов
// Добавлять продукты могут только авторизированные пользователи
// Нужно выполнить GET запрос на адрес http(s)://domen.com/api/<auth_code>/products/append/<categories_id>/<name>/<price>/<?about>
// где <auth_code> - код авторизации пользователя (ОБЯЗАТЕЛЬНЫЙ АРГУМЕНТ)
// где <categories_id> - идентификаторы категории товара через запятую (ОБЯЗАТЕЛЬНЫЙ АРГУМЕНТ) (прим. 1,2,3 и т.д)
// где <name> - название (имя) товара (ОБЯЗАТЕЛЬНЫЙ АРГУМЕНТ)
// где <price> - цена товара (ОБЯЗАТЕЛЬНЫЙ АРГУМЕНТ)
// где <?about> - описание товара (НЕОБЯЗАТЕЛЬНЫЙ АРГУМЕНТ)

Route::get('/{auth_code}/products/append/{categories_id}/{name}/{price}/{about?}', function (Request $request,$auth_code,$categories_id,$name,$price,$about = null) {

    $user = \App\Models\User::where("auth_code",$auth_code)->first();

    if ($user){

        $categories_id = explode(',',$categories_id);

        foreach ($categories_id as $cat) {
            $find = \App\Models\Categorie::where("id",$cat)->exists();
            if($find == null){
                echo "Внимательно проверьте правильность категорий";
                die();
            }
        }

        $findProduct = \App\Models\Product::where("name",$name)->first();
        if ($findProduct == null){
            \App\Models\Product::create(["name" => $name,"price" => $price,"about" => ($about) ? $about : null ]);
        }
        else{
            return "Такой продукт уже существует";
        }

        $lastProduct = \App\Models\Product::orderBy('id','desc')->first();

        foreach ($categories_id as $categorie) {
            \App\Models\CategorieProduct::create(["categorie_id" => $categorie,"product_id" => $lastProduct->id]);
        }

        return "Продукт успешно добавлен";

    }
    else{
        return "Пользователь не найден";
    }

});

// Обновление продукта
// Обновлять продукты могут только авторизированные пользователи
// Нужно выполнить GET запрос на адрес http(s)://domen.com/api/<auth_code>/products/edit/<id_product>/<name>/<price>/<?about>
// где <auth_code> - код авторизации пользователя (ОБЯЗАТЕЛЬНЫЙ АРГУМЕНТ)
// где <id_product> - идентификатор редактируемого продукта (ОБЯЗАТЕЛЬНЫЙ АРГУМЕНТ)
// где <name> - название (имя) товара (ОБЯЗАТЕЛЬНЫЙ АРГУМЕНТ)
// где <price> - цена товара (ОБЯЗАТЕЛЬНЫЙ АРГУМЕНТ)
// где <?about> - описание товара (НЕОБЯЗАТЕЛЬНЫЙ АРГУМЕНТ)

Route::get('/{auth_code}/products/edit/{id_product}/{name}/{price}/{about?}', function (Request $request,$auth_code,$id_product,$name,$price,$about = null) {

    $user = \App\Models\User::where("auth_code",$auth_code)->first();

    if ($user){

        if(\App\Models\Product::where("id",$id_product)->exists()){

            try {
                $findProduct = \App\Models\Product::find($id_product);
                $findProduct->name = $name;
                $findProduct->price = $price;
                $findProduct->about = ($about) ? $about : null;
                $findProduct->save();

                return "Продукт успешно обновлён";
            }
            catch (\Mockery\Exception $exception){
                return $exception->getMessage();
            }

        }
        else{
            echo "Продукт не найден";
        }

        die();


    }
    else{
        return "Пользователь не найден";
    }

});

// Удаление товаров
// Обновлять продукты могут только авторизированные пользователи
// Нужно выполнить GET запрос на адрес http(s)://domen.com/api/<auth_code>/products/delete/<id_product>
// где <auth_code> - код авторизации пользователя (ОБЯЗАТЕЛЬНЫЙ АРГУМЕНТ)
// где <id_product> - идентификатор удаляемого продукта (ОБЯЗАТЕЛЬНЫЙ АРГУМЕНТ)

Route::get('/{auth_code}/products/delete/{id_product}', function (Request $request,$auth_code,$id_product) {

    $user = \App\Models\User::where("auth_code",$auth_code)->first();

    if ($user){

        if(\App\Models\Product::where("id",$id_product)->exists()){

            \App\Models\CategorieProduct::where("product_id",$id_product)->delete();

            $product = \App\Models\Product::find($id_product);
            $product->delete();

            return "Продукт удалён";

        }
        else{
            echo "Продукт не найден";
        }

        die();


    }
    else{
        return "Пользователь не найден";
    }

});
