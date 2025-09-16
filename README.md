<h3>To run the App:</h3>
<b>Prerequisites</b>
<ul>
  <li>PHP 8+</li>
  <li>Symfony cli (optional to serve)</li>
  <li>Composer</li>
  <li>npm</li>
</ul>
<b>Steps</b>
<ul>
  <li>Download Project</li>
  <li>composer install</li>
  <li>php bin/console importmap:install</li>
  <li>php bin/console doctrine:database:create</li>
  <li>php bin/console doctrine:migrations:migrate</li>
  <li>php bin/console doctrine:fixtures:load</li>
  <li>php bin/console cache:clear (optional)</li>
</ul>
