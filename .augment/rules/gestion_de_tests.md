---
type: "agent_requested"
description: "Test directives for code and endpoints"
---
Para pruebas, puedes crear archivos de test relevantes en la carpeta /tests. Asegúrate de que los tests sean funcionales; por ejemplo, no crees tests de autenticación de WordPress sin acceso a credenciales válidas, ni tests de JavaScript que no puedan ser verificados. Si un test no es formal (ej. no usa frameworks como Pest) y solo es para uso temporal, deberás eliminarlo al finalizar para evitar confusiones en el equipo. Para temas de testear endpoint primero haz login y guarda la cookie luego usa CURL
