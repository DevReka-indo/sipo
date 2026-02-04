<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test TinyMCE</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- TinyMCE -->
    <script src="https://cdn.jsdelivr.net/npm/tinymce@7/tinymce.min.js" referrerpolicy="origin"></script>
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Test TinyMCE Implementation</h5>
                    </div>
                    <div class="card-body">
                        <form id="testForm">
                            <div class="mb-3">
                                <label for="test_editor" class="form-label">Isi Surat</label>
                                <textarea id="test_editor" name="content" class="form-control">
                                    <p>Ini adalah contoh isi surat untuk testing TinyMCE.</p>
                                    <p>Anda dapat:</p>
                                    <ul>
                                        <li><strong>Membuat teks tebal</strong></li>
                                        <li><em>Membuat teks miring</em></li>
                                        <li><u>Membuat teks bergaris bawah</u></li>
                                    </ul>
                                </textarea>
                            </div>
                            <div class="mb-3">
                                <button type="button" id="getContent" class="btn btn-primary">Ambil Konten</button>
                                <button type="submit" class="btn btn-success">Submit</button>
                            </div>
                        </form>
                        <div id="result" class="mt-3"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Inisialisasi TinyMCE
        tinymce.init({
            selector: '#test_editor',
            height: 400,
            menubar: false,
            plugins: [
                'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                'insertdatetime', 'media', 'table', 'help', 'wordcount'
            ],
            toolbar: 'undo redo | blocks | bold italic backcolor | ' +
                'alignleft aligncenter alignright alignjustify | ' +
                'bullist numlist outdent indent | removeformat | help',
            content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',
            branding: false,
            setup: function(editor) {
                editor.on('change keyup', function() {
                    editor.save();
                });
            }
        });

        // Test ambil konten
        document.getElementById('getContent').addEventListener('click', function() {
            const content = tinymce.get('test_editor').getContent();
            const textContent = tinymce.get('test_editor').getContent({format: 'text'});

            document.getElementById('result').innerHTML = `
                <div class="alert alert-info">
                    <h6>HTML Content:</h6>
                    <pre>${content}</pre>
                    <h6>Text Content:</h6>
                    <pre>${textContent}</pre>
                    <h6>Length:</h6>
                    <p>HTML: ${content.length} characters</p>
                    <p>Text: ${textContent.trim().length} characters</p>
                </div>
            `;
        });

        // Test form submit
        document.getElementById('testForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const content = tinymce.get('test_editor').getContent();
            alert('Form submitted! Content length: ' + content.length + ' characters');
        });
    </script>
</body>
</html>
