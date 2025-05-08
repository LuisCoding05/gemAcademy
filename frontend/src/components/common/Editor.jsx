import { CKEditor } from '@ckeditor/ckeditor5-react';
import ClassicEditor from '@ckeditor/ckeditor5-build-classic';
import {
  Alignment,
  AutoImage,
  BlockQuote,
  Bold,
  CodeBlock,
  Essentials,
  FontBackgroundColor,
  FontColor,
  FontFamily,
  FontSize,
  Heading,
  Highlight,
  Image,
  ImageCaption,
  ImageStyle,
  ImageToolbar,
  ImageUpload,
  Indent,
  IndentBlock,
  Italic,
  Link,
  List,
  MediaEmbed,
  Paragraph,
  PasteFromOffice,
  Table,
  TableToolbar,
  TextTransformation,
  Underline
} from '@ckeditor/ckeditor5-build-classic';

import '@ckeditor/ckeditor5-build-classic/build/translations/es';
import './../../styles/editor/Editor.css';
import { useTheme } from '../../context/ThemeContext';
import { useCookies } from '../../context/CookieContext';

export default function Editor({ data, onChange, placeholder }) {
  const { isDarkMode } = useTheme();
  const { cookieConsent } = useCookies();

  const editorConfiguration = {
    language: 'es',
    toolbar: {
      items: [
        'undo', 'redo',
        '|', 'heading',
        '|', 'bold', 'italic',
        '|', 'numberedList', 'bulletedList',
        '|', 'indent', 'outdent',
        '|', 'link', 'blockquote', 'insertTable', 
        ...(cookieConsent?.youtube ? ['mediaEmbed'] : [])
      ],
      shouldNotGroupWhenFull: true
    },
    placeholder: placeholder || 'Escribe aquí tu contenido...',
    table: {
      contentToolbar: [
        'tableColumn', 'tableRow', 'mergeTableCells'
      ]
    },
    image: {
      toolbar: [
        'imageStyle:inline',
        'imageStyle:block',
        'imageStyle:side',
        '|',
        'toggleImageCaption',
        'imageTextAlternative',
        '|',
        'linkImage'
      ]
    },
    mediaEmbed: cookieConsent?.youtube ? {
      previewsInData: true,
      providers: [
        {
          name: 'youtube',
          url: [
            /^(?:m\.)?youtube\.com\/watch\?v=([\w-]+)/,
            /^(?:m\.)?youtube\.com\/v\/([\w-]+)/,
            /^youtube\.com\/embed\/([\w-]+)/,
            /^youtu\.be\/([\w-]+)/
          ],
          html: match => {
            const id = match[1];
            return (
              '<div class="video-wrapper">' +
              '<iframe ' +
              'src="https://www.youtube-nocookie.com/embed/' + id + '" ' +
              'loading="lazy" ' +
              'referrerpolicy="strict-origin-when-cross-origin" ' +
              'allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" ' +
              'sandbox="allow-scripts allow-same-origin allow-presentation" ' +
              'frameborder="0" ' +
              'allowfullscreen="true">' +
              '</iframe>' +
              '</div>'
            );
          }
        }
      ]
    } : undefined,
    fontSize: {
      options: [
        9,
        11,
        13,
        'default',
        17,
        19,
        21,
        27,
        35
      ],
      supportAllValues: true
    },
    fontFamily: {
      options: [
        'default',
        'Arial, Helvetica, sans-serif',
        'Courier New, Courier, monospace',
        'Georgia, serif',
        'Lucida Sans Unicode, Lucida Grande, sans-serif',
        'Tahoma, Geneva, sans-serif',
        'Times New Roman, Times, serif',
        'Trebuchet MS, Helvetica, sans-serif',
        'Verdana, Geneva, sans-serif'
      ],
      supportAllValues: true
    },
    fontColor: {
      columns: 6,
      documentColors: 12,
    },
    fontBackgroundColor: {
      columns: 6,
      documentColors: 12,
    }
  };

  return (
    <div className="ckeditor-wrapper">
      <CKEditor
        editor={ClassicEditor}
        config={editorConfiguration}
        data={data}
        onChange={(event, editor) => {
          const content = editor.getData();
          onChange(content);
        }}
      />
      {!cookieConsent?.youtube && (
        <div className="alert alert-info mt-2">
          <small>
            La inserción de videos de YouTube no está disponible porque no has aceptado las cookies de terceros. 
            Puedes cambiar esta configuración en cualquier momento.
          </small>
        </div>
      )}
    </div>
  );
}
