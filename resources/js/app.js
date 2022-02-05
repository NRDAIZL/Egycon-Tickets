require('./bootstrap');

import 'viewerjs/dist/viewer.css';
import Viewer from 'viewerjs';
const gallery = new Viewer(document.getElementById('images'));
console.log(gallery);