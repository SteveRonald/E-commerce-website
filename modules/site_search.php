<?php
header('Content-Type: application/json');
$q = trim($_GET['q'] ?? '');
if (!$q) {
    echo json_encode(['results' => []]);
    exit;
}

$conn = new mysqli("localhost", "root", "", "ecommerce");
$results = [];

// --- 1. Search Products (public info only) ---
$stmt = $conn->prepare("SELECT id, name, description FROM products WHERE name LIKE ? OR description LIKE ? LIMIT 5");
$like = "%$q%";
$stmt->bind_param("ss", $like, $like);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $results[] = [
        'title' => htmlspecialchars($row['name']),
        'summary' => mb_substr(strip_tags($row['description']), 0, 120) . '...',
        'link' => "../modules/shop_main.php#product-" . intval($row['id'])
    ];
}

// --- 2. Search Static Pages ---
$static = [
    [
        'title' => 'About EcoNest',
        'keywords' => ['about', 'mission', 'who are you', 'company', 'ecofriendly', 'ecofriendly values'],
        'summary' => 'Learn about EcoNest, our mission, and our eco-friendly values.',
        'link' => '../pages/about.html'
    ],
    [
        'title' => 'FAQ',
        'keywords' => ['faq', 'help', 'question', 'how', 'return', 'shipping', 'order', 'payment', 'support'],
        'summary' => 'Find answers to common questions about EcoNest, orders, shipping, and more.',
        'link' => '../pages/faq.html'
    ],
    [
        'title' => 'Contact Us',
        'keywords' => ['contact', 'support', 'email', 'phone', 'reach', 'message', 'customer service'],
        'summary' => 'Get in touch with EcoNest for support or questions.',
        'link' => '../pages/contact.html'
    ],
    [
        'title' => 'Register',
        'keywords' => ['register', 'sign up', 'create account', 'join', 'new user'],
        'summary' => 'Create an account to shop and track your orders.',
        'link' => '../modules/register.php'
    ],
    [
        'title' => 'Shop',
        'keywords' => ['shop', 'products', 'buy', 'store', 'eco', 'eco-friendly', 'market'],
        'summary' => 'Browse and buy eco-friendly products from EcoNest.',
        'link' => '../modules/shop_main.php'
    ],
];

foreach ($static as $s) {
    foreach ($s['keywords'] as $kw) {
        if (stripos($q, $kw) !== false) {
            $results[] = [
                'title' => $s['title'],
                'summary' => $s['summary'],
                'link' => $s['link']
            ];
            break;
        }
    }
}

// --- 3. If no results, suggest help ---
if (count($results)) {
    echo json_encode(['results' => $results]);
} else {
    echo json_encode([
        'summary' => "No direct matches found. Try searching for product names, categories, or help topics. You can also visit our <a href='../pages/faq.html' style='color:#2f6b29;text-decoration:underline;'>FAQ</a> or <a href='../pages/contact.html' style='color:#2f6b29;text-decoration:underline;'>Contact</a> page."
    ]);
}
