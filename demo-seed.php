<?php
/**
 * MoveCRM — seed de démonstration.
 * Crée un Bien immobilier démo avec 2 photos attachées, via le conteneur EspoCRM (ORM).
 * Usage: php demo-seed.php
 */
require_once __DIR__ . '/bootstrap.php';

use Espo\Core\Application;

$LOG = __DIR__ . '/.movecrm-demo/seed.log';
function out(string $m) { global $LOG; file_put_contents($LOG, $m . "\n", FILE_APPEND); }
file_put_contents($LOG, "");

try {

$app = new Application();
$app->setupSystemUser();
$container = $app->getContainer();

/** @var \Espo\ORM\EntityManager $em */
$em = $container->getByClass(\Espo\ORM\EntityManager::class);

function makeAttachment($em, string $path, string $name): string {
    $attachment = $em->createEntity('Attachment', [
        'name' => $name,
        'type' => 'image/png',
        'role' => 'Attachment',
        'relatedType' => 'Bien',
        'field' => 'photos',
        'contents' => file_get_contents($path),
    ]);
    return $attachment->getId();
}

$base = __DIR__ . '/.movecrm-demo';
$id1 = makeAttachment($em, "$base/bien-salon.png", 'salon-haussmann.png');
$id2 = makeAttachment($em, "$base/bien-facade.png", 'facade-immeuble.png');

out("Attachments: $id1 / $id2");

$bien = $em->createEntity('Bien', [
    'name' => 'Appartement T3 Haussmannien — Paris 9e',
    'reference' => 'MV-2026-001',
    'propertyType' => 'Appartement',
    'transactionType' => 'Vente',
    'status' => 'En vente',
    'price' => 645000,
    'fees' => 25800,
    'surface' => 72.0,
    'rooms' => 3,
    'bedrooms' => 2,
    'floor' => 4,
    'yearBuilt' => 1895,
    'dpe' => 'D',
    'ges' => 'D',
    'description' => "Bel appartement traversant au 4e étage avec ascenseur. Moulures, parquet, cheminée en marbre. Proche métro et commerces.",
    'addressStreet' => '12 rue de Châteaudun',
    'addressCity' => 'Paris',
    'addressPostalCode' => '75009',
    'addressCountry' => 'France',
    'photosIds' => [$id1, $id2],
]);

out("Bien créé: " . $bien->getId() . " — " . $bien->get('name'));
out("Photos liées: " . count($bien->getLinkMultipleIdList('photos')));

} catch (\Throwable $e) {
    out("ERREUR: " . get_class($e) . ": " . $e->getMessage());
    out($e->getFile() . ":" . $e->getLine());
}
