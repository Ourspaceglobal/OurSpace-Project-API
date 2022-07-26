# OurSpace
[Technical Document](https://docs.google.com/document/d/1dSc9IASnSdUX0EZTCUG-4mtS08lG3h6DO0ZWzbZwX6Y/edit?usp=sharing)

## Project setup
1. Install all dependencies
```
composer install
```
If you are on production, run `composer install --no-dev` to skip the development dependencies.
2. Provision the environment variables
```
cp .env.example .env
```
- Ensure you keep the `QUEUE_CONNECTION=database` since many notifications use the database for **"in-app"** use.
- The Google Geocode API key is required for the geocoding feature.
- Paystack is the only payment gateway in the application, hence, the keys should be provided.
3. Set the application key and storage link
```
composer run-script post-create-project-cmd
```
4. Run your migrations
```
php artisan migrate --force
```
5. Run the seeds
If this is the first application, you may create the default admin user first.
```
php artisan db:seed --class=AdminSeeder
```
Subsequently, you may run this for permissions and default role for the admin user:
```
php artisan db:seed --class=PermissionSeeder
```
6. Run the server

##### Bonus
In production, this script `composer run-script post-project-live` helps cache all necessary files for the application performance.

## Notes
1. All models must use the `MorphMapTrait` trait.
    This is because we are enforcing morph maps across the application. Hence, `App\Models\Apartment` is stored as `apartment` in the database, and Eloquent helps us to resolve.
2. All models that can be reviewed must use the `Reviewable` trait and the table must have a `rating` column.
   This is to manage the average rating of the model within the model.
3. For any new `Illuminate\Foundation\Auth\User as Authenticatable` model (user), you must ensure you add a new column named `code` to the `password_resets` table you create for that model.
   - Password resets are available via token or code.
   - It is also essential to create the `sendPasswordResetNotification()` in the model. *@see App\Models\User.php*
4. Queue names. The following queues are available to deliver notifications across the application in terms of their priority. It is important to make sure that if you are using supervisor, you are passing the correct queue name(s) to the `php artisan queue:work` command. For instance, `php artisan queue:work --queue=default,notifications` OR you can create two workers to handle both queues separately. It all depends on the server's capacity and choice.
   - Default
   - Notifications
