TypeError
vendor\filament\tables\src\Table\Concerns\CanSortRecords.php:23
Filament\Tables\Table::defaultSort(): Argument #1 ($column) must be of type Closure|string|null, array given, called in C:\laragon\www\ulife\app\Filament\Resources\Analytics\UserAnalytics\Pages\ListUserAnalytics.php on line 73# Fix GROUP BY Error in UserAnalytics

## Steps:
- [x] 1. Edit app/Filament/Resources/Analytics/UserAnalytics/Pages/ListUserAnalytics.php to fix query (add sort_id aggregate, update orderBy and defaultSort)
- [x] 2. Clear caches: php artisan cache:clear, php artisan filament:cache-components, php artisan view:clear
- [x] 3. Test User Analytics page in Filament admin
- [x] 4. Mark complete

Current status: FIXED! GROUP BY and defaultSort resolved, page works.
