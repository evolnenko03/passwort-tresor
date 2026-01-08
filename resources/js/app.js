import './bootstrap';

import Alpine from 'alpinejs';
import { passwordGenerator } from './passwords/generator';

window.Alpine = Alpine;

Alpine.data('passwordGenerator', passwordGenerator);

Alpine.start();
