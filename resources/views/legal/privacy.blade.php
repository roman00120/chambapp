@extends('layouts.public')

@section('title', 'Aviso de privacidad | Chambapp')
@section('meta_description', 'Información de referencia sobre privacidad y datos personales en Chambapp.')

@section('content')
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-lg-8">
                    <p class="eyebrow">Información legal</p>
                    <h1 class="page-title">Aviso de privacidad</h1>
                    <div class="ui-card mt-4 p-4">
                        <p><strong>Borrador sujeto a revisión jurídica.</strong> Esta página todavía no sustituye el aviso de privacidad definitivo del responsable.</p>
                        <p>Para verificar la identidad de profesionales, Chambapp utiliza a Didit como proveedor externo. Según el flujo configurado, Didit puede tratar imágenes de un documento oficial, fotografía o selfie, comparación facial, prueba de vida y señales de prevención de fraude.</p>
                        <p>La captura ocurre en la sesión alojada por Didit. Chambapp está diseñado para conservar únicamente el estado de la verificación, una referencia técnica, fechas, códigos de resultado seguros, consentimiento y auditoría mínima. Chambapp no almacena copias del documento, selfies, videos ni biometría cruda.</p>
                        <p>No se afirma que Didit consulte o valide el documento contra una base gubernamental específica. La verificación del documento y una consulta gubernamental no son equivalentes.</p>
                        <p class="mb-0">La versión legal final debe completar la identidad y domicilio del responsable, finalidades completas, transferencias, plazos de conservación, mecanismos ARCO y canales de atención antes del lanzamiento comercial definitivo.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
