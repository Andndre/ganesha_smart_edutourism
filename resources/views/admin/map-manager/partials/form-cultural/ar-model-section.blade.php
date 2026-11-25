@include('admin.cultural-objects.partials.ar-assets', ['assetModel' => $object->arModel ?? null, 'assetPrefix' => isset($object) ? 'cultural-'.$object->id : 'cultural-new'])
