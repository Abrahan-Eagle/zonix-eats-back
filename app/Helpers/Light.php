<?php

if (!function_exists('Light')) {
    /**
     * Obtener el tema (light/dark) del usuario autenticado
     * Implementación igual que uniblockx - retorna el valor directo de la BD
     * La columna light es ENUM(1, 0) donde 1 = light, 0 = dark
     * 
     * @return string|null Retorna '1' (light), '0' (dark), o null si no hay usuario
     */
    function Light()
    {
        if (!empty(auth()->user())) {
            $lightdark = auth()->user()->light;
            return $lightdark;
        }
        
        return null;
    }
}
