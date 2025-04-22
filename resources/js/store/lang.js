import { ref } from "vue";
import { defineStore } from "pinia";

export const langStore = defineStore("langStore", () => {
    // Establecer 'es' como valor por defecto si no hay ninguno guardado
    let locale = ref(window.config?.locale || 'es');
    let locales = ref(window.config?.locales || ['es', 'en']);

    function setLocale(l) {
        locale.value = l;
        // Guardar la preferencia en localStorage
        localStorage.setItem('userLocale', l);
    }

    // Inicializar con el valor guardado o 'es' por defecto
    const savedLocale = localStorage.getItem('userLocale');
    if (savedLocale) {
        locale.value = savedLocale;
    }

    return { locale, locales, setLocale };
}, {persist: true});


