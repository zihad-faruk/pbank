## About

This console application takes a csv file as input which contains operation data, processes the data and calculates
commission fee for each of the row. Calculation of Deposit and Business Withdraw operation is pretty straightforward .
So, Algorithm to calculate commission fee for Private Withdraw is described below:

### Algorithm used for Private Withdraw

- First calculate User Identification key using User Id and Operation days week and Year e.g. 4_12015 . Here , the
  format used is
  ```userId_WeekYear```
- For each withdraw operation , will keep the identification key (```userId_WeekYear```) and count of the same key as
  key=>value pair ( Same key means , same operation week)
- If the key doesn't exist , insert it and assign the count to 1
- If the key exists , increment the count .
- If the count of the key is less the or equal 3 ,
    - Will check if this key exists in the discount array or not
        - If not use the default discount i.e 1000 EUR
        - If exists use the discount value from the discount array
    - Convert the discount to the operation currency
    - Deduct converted discount fee from the operation amount
    - After deducting , keep the remaining discount amount in a key pair value where User Identification Key is the main
      key
      after reverting it to ``EUR`` .
- If the count is greater than 3, proceed with the operation amount
- Calculate 0.3% commission from the calculated amount
- After calculating the commission , format the result using custom round rule .

## Prerequisites

The system is based on ```LARAVEL 8.0``` . So ```php 7.4 or higher``` is required . Also ``composer`` is required .

## How to install

Please clone this git repository using below command

```
git clone https://github.com/zihad-faruk/pbank.git
```

Then navigate to project directory (in default case ``pbank``)

```
cd pbank
```

After that run

```
composer install
```

## How to use

The basic command to use the script is :

```
php artisan calculate:commission {path to your CSV file}
```

Here you can specify the full path of the file

```
php artisan calculate:commission C:/xampp/htdocs/pbank/input.csv
```

or only the name of the file , in that case you need to upload the file to projects
```public``` folder and then use the following command

```
php artisan calculate:commission input.csv -P 
```

or

```
php artisan calculate:commission input.csv --public
```

## How to test

To test the tool , please run the below command :

```
./vendor/bin/phpunit --filter CommissionTest
```

It will run two tests , one to test the console command .
The other takes input from ```/public/Test/test_data.csv``` and validates with data
from ```/public/Test/test_output_data.csv``` .
> **Note**
> In the test of calculating commission , the conversion rate isn't fetched from the API . Instead , it's based on
> conversion rate provided in the requirement .

## Files List

The files in which the main relevant codebase resides are listed below:

- ```/app/Console/Commands/CalculateCommission.php```
- ```/app/Http/Controllers/ProcessCommissionController.php```
- ```/app/Traits/*```
- ```/app/Services/*```
- ```/app/Interfaces/*```
- ```/tests/Feature/CommissionTest.php```