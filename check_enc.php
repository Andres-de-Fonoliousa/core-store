$c = App\Models\Category::find(164);
echo 'bin2hex: ' . bin2hex($c->name) . PHP_EOL;
echo 'json: ' . json_encode($c->name, JSON_UNESCAPED_UNICODE) . PHP_EOL;
echo 'All:' . PHP_EOL;
foreach (App\Models\Category::all() as $cat) {
    echo $cat->id . '|' . bin2hex($cat->name) . '|' . json_encode($cat->name, JSON_UNESCAPED_UNICODE) . PHP_EOL;
}
echo 'Done' . PHP_EOL;
