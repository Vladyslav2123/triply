<?php

namespace Database\Factories;

use App\Actions\Photo\CreatePhoto;
use App\Enums\EducationLevel;
use App\Enums\Gender;
use App\Enums\Interest;
use App\Enums\Language;
use App\Models\Profile;
use App\Models\User;
use App\ValueObjects\Address;
use App\ValueObjects\Coordinates;
use App\ValueObjects\Location;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Random\RandomException;
use Symfony\Component\Uid\Ulid;

/**
 * @extends Factory<Profile>
 */
class ProfileFactory extends Factory
{
    protected $model = Profile::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     *
     * @throws RandomException
     */
    public function definition(): array
    {
        $languages = fake()->randomElements(Language::all(), random_int(1, 3));
        $travelTypes = [
            'beach' => 'Пляжний відпочинок',
            'mountains' => 'Гірський туризм',
            'city' => 'Міські подорожі',
            'countryside' => 'Сільський туризм',
            'adventure' => 'Пригодницький туризм',
            'cultural' => 'Культурний туризм',
            'eco' => 'Екотуризм',
        ];

        $pets = [
            'dog' => 'Собака',
            'cat' => 'Кіт',
            'fish' => 'Рибки',
            'bird' => 'Птахи',
            'hamster' => 'Хом\'як',
            'none' => 'Немає',
        ];

        $aboutTemplates = [
            'Привіт! Я %s, працюю %s. Люблю подорожувати та відкривати нові місця. %s',
            'Мандрівник у душі, %s за професією. Обожнюю %s та %s. Завжди відкритий до нових знайомств!',
            'Досліджую світ через подорожі та нові знайомства. Професійно займаюсь %s. %s',
        ];

        $hobbies = [
            'фотографією',
            'йогою',
            'кулінарією',
            'музикою',
            'спортом',
            'малюванням',
            'читанням',
        ];

        $firstName = $this->faker->firstName();
        $jobTitle = $this->faker->jobTitle();
        $hobby1 = $this->faker->randomElement($hobbies);
        $hobby2 = $this->faker->randomElement(array_diff($hobbies, [$hobby1]));

        $about = sprintf(
            $this->faker->randomElement($aboutTemplates),
            $firstName,
            $jobTitle,
            $hobby1,
            $hobby2,
            $this->faker->randomElement(['подорожі', 'нові враження', 'активний відпочинок'])
        );

        return [
            'user_id' => fn () => Ulid::generate(),
            'first_name' => $firstName,
            'last_name' => $this->faker->lastName(),
            'birth_date' => $this->faker->dateTimeBetween('-45 years', '-18 years'),

            'gender' => $this->faker->randomElement(Gender::all()),
            'is_superhost' => $this->faker->boolean(20),
            'response_speed' => $this->faker->randomFloat(1, 85, 100),

            'views_count' => $this->faker->numberBetween(50, 5000),
            'rating' => $this->faker->randomFloat(1, 4.0, 5.0),
            'reviews_count' => $this->faker->numberBetween(5, 500),

            'work' => substr($jobTitle, 0, 50),
            'job_title' => substr($jobTitle, 0, 50),
            'company' => substr($this->faker->company(), 0, 50),
            'school' => substr($this->faker->randomElement([
                'Київський національний університет',
                'Львівська політехніка',
                'Харківський університет',
                'Одеський університет',
            ]), 0, 50),
            'education_level' => $this->faker->randomElement(EducationLevel::all()),

            'dream_destination' => $this->faker->randomElement([
                'Ісландія',
                'Нова Зеландія',
                'Японія',
                'Норвегія',
                'Австралія',
                'Канада',
            ]),
            'next_destinations' => $this->faker->randomElements([
                'Італія',
                'Іспанія',
                'Франція',
                'Греція',
                'Хорватія',
                'Португалія',
            ], 2),
            'travel_history' => $this->faker->boolean(70),
            'favorite_travel_type' => $this->faker->randomElement(array_keys($travelTypes)),

            'time_spent_on' => $this->faker->numberBetween(2, 8),
            'useless_skill' => $this->faker->randomElement([
                'Складання кубика Рубіка',
                'Свист на листочку',
                'Битбокс',
            ]),
            'pets' => $this->faker->randomElement(array_keys($pets)),
            'birth_decade' => $this->faker->boolean(),
            'favorite_high_school_song' => substr($this->faker->randomElement([
                'Queen - Bohemian Rhapsody',
                'Океан Ельзи - Там, де нас нема',
                'RHCP - Californication',
                'Nirvana - Smells Like Teen Spirit',
            ]), 0, 50),
            'fun_fact' => substr($this->faker->randomElement([
                'Можу заснути будь-де і будь-коли',
                'Колекціоную магніти з різних міст',
                'Жодного разу не був у McDonald\'s',
                'Вмію готувати найкращий борщ у світі',
            ]), 0, 255),
            'obsession' => substr($this->faker->randomElement([
                'Кава',
                'Фотографія',
                'Подорожі',
                'Книги',
                'Спорт',
            ]), 0, 50),
            'biography_title' => substr($this->faker->randomElement([
                'Життя - найкраща пригода',
                'В пошуках нових вражень',
                'Подорож довжиною в життя',
                'Колекціонер моментів',
            ]), 0, 50),

            'languages' => $languages,
            'about' => substr($about, 0, 255),
            'interests' => $this->faker->randomElements(Interest::all(), 5),

            'location' => $this->generateLocation(),

            'facebook_url' => 'https://facebook.com/'.Str::slug($firstName),
            'instagram_url' => 'https://instagram.com/'.Str::slug($firstName),
            'twitter_url' => 'https://twitter.com/'.Str::slug($firstName),
            'linkedin_url' => 'https://linkedin.com/in/'.Str::slug($firstName),

            'email_notifications' => $this->faker->boolean(80),
            'sms_notifications' => $this->faker->boolean(60),
            'preferred_language' => $this->faker->randomElement(['uk', 'en']),
            'preferred_currency' => $this->faker->randomElement(['UAH', 'USD', 'EUR']),

            'is_verified' => $this->faker->boolean(70),
            'verified_at' => $this->faker->boolean(70) ? $this->faker->dateTimeBetween('-1 year') : null,
            'verification_method' => $this->faker->randomElement(['email', 'phone', 'document', null]),
            'last_active_at' => $this->faker->dateTimeBetween('-1 month'),
        ];
    }

    private function generateLocation(): Location
    {
        $cities = [
            'Київ' => ['50.4501', '30.5234'],
            'Львів' => ['49.8397', '24.0297'],
            'Одеса' => ['46.4825', '30.7233'],
            'Харків' => ['49.9935', '36.2304'],
            'Дніпро' => ['48.4647', '35.0462'],
        ];

        $city = array_rand($cities);
        $coordinates = $cities[$city];

        $streets = [
            'вул. Хрещатик',
            'вул. Шевченка',
            'вул. Франка',
            'вул. Лесі Українки',
            'вул. Сагайдачного',
            'вул. Володимирська',
        ];

        $address = new Address(
            $this->faker->randomElement($streets),
            $city,
            sprintf('%05d', $this->faker->numberBetween(1000, 99999)),
            'Україна',
            $this->faker->randomElement(['Київська обл.', 'Львівська обл.', 'Одеська обл.', 'Харківська обл.', 'Дніпропетровська обл.']),
        );

        $coordinates = new Coordinates(
            $coordinates[0],
            $coordinates[1]
        );

        return new Location($address, $coordinates);
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Profile $profile) {
            app(CreatePhoto::class)->execute($profile);
        });
    }

    /**
     * Configure the factory to create a profile for a specific user.
     *
     * @param  User  $user  The user to create a profile for
     */
    public function forUser(User $user): static
    {
        return $this->state(function () use ($user) {
            return [
                'user_id' => $user->id,
            ];
        });
    }
}
