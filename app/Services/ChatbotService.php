<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Category;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\Client;
use App\Models\Detail;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ChatbotService
{
    /**
     * Procesa una pregunta del usuario y devuelve una respuesta basada en la base de datos
     */
    public function processQuestion(string $question): array
    {
        $question = strtolower(trim($question));

        // Normalizar la pregunta para análisis
        $question = $this->normalizeQuestion($question);

        // Intentar identificar el tipo de consulta
        $intent = $this->detectIntent($question);

        try {
            return match($intent) {
                'productos_buscar' => $this->searchProducts($question),
                'productos_stock' => $this->checkStock($question),
                'productos_categoria' => $this->productsByCategory($question),
                'productos_sin_stock' => $this->productsWithoutStock(),
                'productos_poco_stock' => $this->productsLowStock($question),
                'ventas_total' => $this->getTotalSales($question),
                'ventas_dia' => $this->getSalesByDay($question),
                'ventas_mes' => $this->getSalesByMonth($question),
                'productos_mas_vendidos' => $this->topProducts(),
                'inventario_valorizado' => $this->inventoryValue(),
                'ticket_promedio' => $this->averageTicket(),
                'categorias_listar' => $this->listCategories(),
                'producto_por_codigo' => $this->getProductByCode($question),
                default => $this->defaultResponse($question)
            };
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Lo siento, hubo un error al procesar tu consulta.',
                'data' => null,
                'error' => config('app.debug') ? $e->getMessage() : null
            ];
        }
    }

    /**
     * Normaliza la pregunta eliminando caracteres especiales
     */
    private function normalizeQuestion(string $question): string
    {
        return preg_replace('/[^a-z0-9\s]/', '', $question);
    }

    /**
     * Detecta la intención del usuario basándose en palabras clave
     */
    private function detectIntent(string $question): string
    {
        // Búsqueda de productos
        if (preg_match('/\b(buscar|encontrar|existe|hay|tienes)\b.*\b(producto|productos)\b/i', $question)) {
            if (preg_match('/\b(codigo|code)\b/i', $question)) {
                return 'producto_por_codigo';
            }
            return 'productos_buscar';
        }

        // Stock de productos
        if (preg_match('/\b(stock|cantidad|unidades|existencias)\b.*\b(producto|productos)\b/i', $question)) {
            if (preg_match('/\b(sin|cero|ninguno)\b/i', $question)) {
                return 'productos_sin_stock';
            }
            if (preg_match('/\b(poco|bajo|menor|menos)\b/i', $question)) {
                return 'productos_poco_stock';
            }
            return 'productos_stock';
        }

        // Productos por categoría
        if (preg_match('/\b(producto|productos)\b.*\b(categoria|categorias)\b/i', $question)) {
            return 'productos_categoria';
        }

        // Ventas
        if (preg_match('/\b(venta|ventas|ventas del dia|ventas hoy|ventas del mes|ingresos)\b/i', $question)) {
            if (preg_match('/\b(dia|hoy|hoy dia)\b/i', $question)) {
                return 'ventas_dia';
            }
            if (preg_match('/\b(mes|mensual)\b/i', $question)) {
                return 'ventas_mes';
            }
            return 'ventas_total';
        }

        // Productos más vendidos
        if (preg_match('/\b(mas vendido|mas vendidos|top|mejor|popular)\b/i', $question)) {
            return 'productos_mas_vendidos';
        }

        // Inventario valorizado
        if (preg_match('/\b(inventario|valorizado|valor del inventario|stock valorizado)\b/i', $question)) {
            return 'inventario_valorizado';
        }

        // Ticket promedio
        if (preg_match('/\b(ticket promedio|promedio de venta|venta promedio)\b/i', $question)) {
            return 'ticket_promedio';
        }

        // Categorías
        if (preg_match('/\b(categoria|categorias|listar categorias)\b/i', $question)) {
            return 'categorias_listar';
        }

        return 'default';
    }

    /**
     * Busca productos por nombre o código
     */
    private function searchProducts(string $question): array
    {
        // Extraer término de búsqueda
        $terms = explode(' ', $question);
        $searchTerm = '';
        foreach ($terms as $term) {
            if (!in_array($term, ['buscar', 'encontrar', 'existe', 'hay', 'tienes', 'producto', 'productos', 'de', 'el', 'la', 'los', 'las'])) {
                $searchTerm = $term;
                break;
            }
        }

        if (empty($searchTerm)) {
            return [
                'success' => false,
                'message' => 'Por favor, especifica el nombre o código del producto que deseas buscar.',
                'data' => null
            ];
        }

        $products = Product::where('name', 'LIKE', "%{$searchTerm}%")
            ->orWhere('code', 'LIKE', "%{$searchTerm}%")
            ->with('category')
            ->get();

        if ($products->isEmpty()) {
            return [
                'success' => false,
                'message' => "No se encontraron productos que coincidan con '{$searchTerm}'.",
                'data' => null
            ];
        }

        return [
            'success' => true,
            'message' => "Se encontraron {$products->count()} producto(s):",
            'data' => $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'codigo' => $product->code ?? 'N/A',
                    'nombre' => $product->name,
                    'precio' => number_format($product->price ?? 0, 2),
                    'stock' => $product->quantity ?? 0,
                    'categoria' => $product->category->name ?? 'Sin categoría'
                ];
            })
        ];
    }

    /**
     * Verifica stock de un producto específico
     */
    private function checkStock(string $question): array
    {
        $terms = explode(' ', $question);
        $searchTerm = '';
        foreach ($terms as $term) {
            if (!in_array($term, ['stock', 'cantidad', 'unidades', 'existencias', 'de', 'el', 'la', 'producto', 'productos'])) {
                $searchTerm = $term;
                break;
            }
        }

        if (empty($searchTerm)) {
            $products = Product::select('name', 'quantity', 'code')
                ->orderBy('quantity', 'asc')
                ->limit(10)
                ->get();

            return [
                'success' => true,
                'message' => 'Productos con menor stock:',
                'data' => $products->map(function ($product) {
                    return [
                        'nombre' => $product->name,
                        'codigo' => $product->code ?? 'N/A',
                        'stock' => $product->quantity ?? 0
                    ];
                })
            ];
        }

        $product = Product::where('name', 'LIKE', "%{$searchTerm}%")
            ->orWhere('code', 'LIKE', "%{$searchTerm}%")
            ->first();

        if (!$product) {
            return [
                'success' => false,
                'message' => "No se encontró el producto '{$searchTerm}'.",
                'data' => null
            ];
        }

        return [
            'success' => true,
            'message' => "Stock del producto '{$product->name}':",
            'data' => [
                'nombre' => $product->name,
                'codigo' => $product->code ?? 'N/A',
                'stock' => $product->quantity ?? 0,
                'precio' => number_format($product->price ?? 0, 2)
            ]
        ];
    }

    /**
     * Lista productos por categoría
     */
    private function productsByCategory(string $question): array
    {
        // Extraer nombre de categoría
        $terms = explode(' ', $question);
        $categoryName = '';
        foreach ($terms as $term) {
            if (!in_array($term, ['producto', 'productos', 'categoria', 'categorias', 'de', 'en', 'la', 'el'])) {
                $categoryName = $term;
                break;
            }
        }

        if (empty($categoryName)) {
            $categories = Category::withCount('products')->get();
            return [
                'success' => true,
                'message' => 'Categorías disponibles:',
                'data' => $categories->map(function ($category) {
                    return [
                        'nombre' => $category->name,
                        'productos' => $category->products_count ?? 0
                    ];
                })
            ];
        }

        $category = Category::where('name', 'LIKE', "%{$categoryName}%")->first();

        if (!$category) {
            return [
                'success' => false,
                'message' => "No se encontró la categoría '{$categoryName}'.",
                'data' => null
            ];
        }

        $products = Product::where('category_id', $category->id)->get();

        return [
            'success' => true,
            'message' => "Productos en la categoría '{$category->name}' ({$products->count()}):",
            'data' => $products->map(function ($product) {
                return [
                    'nombre' => $product->name,
                    'codigo' => $product->code ?? 'N/A',
                    'stock' => $product->quantity ?? 0,
                    'precio' => number_format($product->price ?? 0, 2)
                ];
            })
        ];
    }

    /**
     * Productos sin stock
     */
    private function productsWithoutStock(): array
    {
        $products = Product::where('quantity', '<=', 0)->get();

        return [
            'success' => true,
            'message' => $products->isEmpty()
                ? '¡Excelente! No hay productos sin stock.'
                : "Productos sin stock ({$products->count()}):",
            'data' => $products->map(function ($product) {
                return [
                    'nombre' => $product->name,
                    'codigo' => $product->code ?? 'N/A',
                    'stock' => $product->quantity ?? 0
                ];
            })
        ];
    }

    /**
     * Productos con poco stock (menos de 10 unidades)
     */
    private function productsLowStock(string $question): array
    {
        // Intentar extraer umbral
        preg_match('/\b(\d+)\b/', $question, $matches);
        $threshold = $matches[1] ?? 10;

        $products = Product::where('quantity', '<=', $threshold)
            ->where('quantity', '>', 0)
            ->orderBy('quantity', 'asc')
            ->get();

        return [
            'success' => true,
            'message' => "Productos con stock menor o igual a {$threshold} unidades ({$products->count()}):",
            'data' => $products->map(function ($product) {
                return [
                    'nombre' => $product->name,
                    'codigo' => $product->code ?? 'N/A',
                    'stock' => $product->quantity ?? 0
                ];
            })
        ];
    }

    /**
     * Ventas totales
     */
    private function getTotalSales(string $question): array
    {
        $total = Sale::sum('total') ?? 0;
        $count = Sale::count();

        return [
            'success' => true,
            'message' => 'Resumen de ventas:',
            'data' => [
                'total_ventas' => number_format($total, 2),
                'cantidad_ventas' => $count
            ]
        ];
    }

    /**
     * Ventas del día
     */
    private function getSalesByDay(string $question): array
    {
        $today = Carbon::today();
        $sales = Sale::whereDate('created_at', $today)->get();
        $total = $sales->sum('total') ?? 0;

        return [
            'success' => true,
            'message' => "Ventas de hoy ({$today->format('d/m/Y')}):",
            'data' => [
                'total' => number_format($total, 2),
                'cantidad' => $sales->count()
            ]
        ];
    }

    /**
     * Ventas del mes
     */
    private function getSalesByMonth(string $question): array
    {
        $month = Carbon::now()->month;
        $year = Carbon::now()->year;

        $sales = Sale::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->get();

        $total = $sales->sum('total') ?? 0;

        return [
            'success' => true,
            'message' => "Ventas del mes ({$month}/{$year}):",
            'data' => [
                'total' => number_format($total, 2),
                'cantidad' => $sales->count()
            ]
        ];
    }

    /**
     * Top productos más vendidos
     */
    private function topProducts(): array
    {
        $topProducts = DB::table('details')
            ->select('products.id', 'products.name', DB::raw('SUM(details.quantity) as qty_sold'))
            ->join('products', 'products.id', '=', 'details.product_id')
            ->groupBy('products.id', 'products.name')
            ->orderBy('qty_sold', 'desc')
            ->limit(10)
            ->get();

        return [
            'success' => true,
            'message' => 'Top 10 productos más vendidos:',
            'data' => $topProducts->map(function ($product) {
                return [
                    'nombre' => $product->name,
                    'cantidad_vendida' => $product->qty_sold
                ];
            })
        ];
    }

    /**
     * Valor del inventario
     */
    private function inventoryValue(): array
    {
        $result = Product::select(
            DB::raw('SUM(quantity) as total_items'),
            DB::raw('SUM(quantity * price) as inventory_value')
        )->first();

        return [
            'success' => true,
            'message' => 'Valorización del inventario:',
            'data' => [
                'total_items' => number_format($result->total_items ?? 0, 0),
                'valor_total' => number_format($result->inventory_value ?? 0, 2)
            ]
        ];
    }

    /**
     * Ticket promedio
     */
    private function averageTicket(): array
    {
        $average = Sale::avg('total') ?? 0;

        return [
            'success' => true,
            'message' => 'Ticket promedio de ventas:',
            'data' => [
                'promedio' => number_format($average, 2)
            ]
        ];
    }

    /**
     * Lista categorías
     */
    private function listCategories(): array
    {
        $categories = Category::withCount('products')->get();

        return [
            'success' => true,
            'message' => 'Categorías disponibles:',
            'data' => $categories->map(function ($category) {
                return [
                    'nombre' => $category->name,
                    'productos' => $category->products_count ?? 0
                ];
            })
        ];
    }

    /**
     * Busca producto por código
     */
    private function getProductByCode(string $question): array
    {
        preg_match('/\b([A-Z0-9-]+)\b/i', $question, $matches);
        $code = $matches[1] ?? null;

        if (!$code) {
            return [
                'success' => false,
                'message' => 'Por favor, proporciona el código del producto.',
                'data' => null
            ];
        }

        $product = Product::where('code', $code)->with('category')->first();

        if (!$product) {
            return [
                'success' => false,
                'message' => "No se encontró un producto con el código '{$code}'.",
                'data' => null
            ];
        }

        return [
            'success' => true,
            'message' => "Información del producto con código '{$code}':",
            'data' => [
                'codigo' => $product->code,
                'nombre' => $product->name,
                'precio' => number_format($product->price ?? 0, 2),
                'stock' => $product->quantity ?? 0,
                'categoria' => $product->category->name ?? 'Sin categoría'
            ]
        ];
    }

    /**
     * Respuesta por defecto
     */
    private function defaultResponse(string $question): array
    {
        return [
            'success' => false,
            'message' => 'No entendí tu pregunta. Puedo ayudarte con:',
            'data' => [
                'ayuda' => [
                    'Buscar productos por nombre o código',
                    'Consultar stock de productos',
                    'Ver productos sin stock o con poco stock',
                    'Listar productos por categoría',
                    'Consultar ventas del día, mes o totales',
                    'Ver productos más vendidos',
                    'Consultar valorización del inventario',
                    'Ver ticket promedio de ventas',
                    'Listar categorías disponibles'
                ]
            ]
        ];
    }
}

