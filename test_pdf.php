try {
$pdf = Barryvdh\DomPDF\Facade\Pdf::loadHTML('<h1>Test</h1>');
$pdf->save(storage_path('test.pdf'));
echo "PDF Generated Successfully at " . storage_path('test.pdf');
} catch (\Exception $e) {
echo "Error: " . $e->getMessage();
}