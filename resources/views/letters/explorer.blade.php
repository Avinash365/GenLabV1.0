<!DOCTYPE html>
<html>
<head>
    <title>Letters Explorer</title>
    <style>
        body {
            font-family: Arial;
            background: #f5f6fa;
            padding: 20px;
        }

        ul {
            list-style-type: none;
            padding-left: 20px;
        }

        .folder {
            cursor: pointer;
            font-weight: bold;
            color: #2f3640;
        }

        .file {
            color: #353b48;
        }

        .hidden {
            display: none;
        }
    </style>
</head>
<body>

<h2>📁 Letters Explorer</h2>

{{-- Start Tree --}}
<ul>
    @include('letters.partials.node', ['node' => $tree])
</ul>

<script>
    function toggle(element) {
        let next = element.nextElementSibling;
        next.classList.toggle('hidden');
    }
</script>

</body>
</html>
