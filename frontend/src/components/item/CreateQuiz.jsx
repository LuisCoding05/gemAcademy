import React, { useState } from 'react';
import { useTheme } from '../../context/ThemeContext';
import axios from '../../utils/axios';
import Icon from '../Icon';
import Loader from '../common/Loader';
import QuizForm from './QuizForm';

const CreateQuiz = ({ courseId, onCreated, onCancel }) => {
    const { isDarkMode } = useTheme();
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);

    const handleSubmit = async (formData) => {
        try {
            setLoading(true);
            setError(null);

            // Validación básica
            if (!formData.titulo?.trim()) {
                throw new Error('El título es obligatorio');
            }

            if (!formData.fechaLimite) {
                throw new Error('La fecha límite es obligatoria');
            }

            if (!formData.preguntas?.length) {
                throw new Error('Debe añadir al menos una pregunta');
            }

            // Validar que cada pregunta tenga al menos 2 opciones y una correcta
            formData.preguntas.forEach((pregunta, index) => {
                if (!pregunta.pregunta?.trim()) {
                    throw new Error(`La pregunta ${index + 1} no tiene texto`);
                }

                if (!pregunta.opciones?.length || pregunta.opciones.length < 2) {
                    throw new Error(`La pregunta ${index + 1} debe tener al menos 2 opciones`);
                }

                const tieneOpcionCorrecta = pregunta.opciones.some(opcion => opcion.esCorrecta);
                if (!tieneOpcionCorrecta) {
                    throw new Error(`La pregunta ${index + 1} debe tener al menos una opción correcta`);
                }
            });

            // Enviar al backend
            const response = await axios.post(`/api/item/${courseId}/quiz/create`, formData);
            
            // Si el quiz se creó correctamente, añadir las preguntas
            if (response.data.id) {
                const quizId = response.data.id;
                
                // Añadir preguntas una por una
                for (const pregunta of formData.preguntas) {
                    await axios.post(`/api/item/${courseId}/quiz/${quizId}/preguntas`, {
                        pregunta: pregunta.pregunta,
                        puntos: pregunta.puntos,
                        orden: pregunta.orden,
                        opciones: pregunta.opciones
                    });
                }
            }

            onCreated(response.data);
        } catch (error) {
            setError(error.response?.data?.message || error.message || 'Error al crear el quiz');
            setLoading(false);
        }
    };

    return (
        <div className={`create-quiz mb-4 ${isDarkMode ? 'text-light' : ''}`}>
            {error && (
                <div className="alert alert-danger mb-3">
                    {error}
                </div>
            )}

            {loading ? (
                <div className="text-center">
                    <Loader />
                    <p>Creando quiz...</p>
                </div>
            ) : (
                <QuizForm 
                    onSubmit={handleSubmit}
                    onCancel={onCancel}
                    initialData={{
                        titulo: '',
                        descripcion: '',
                        fechaLimite: '',
                        tiempoLimite: 30,
                        intentosPermitidos: 1,
                        preguntas: []
                    }}
                />
            )}
        </div>
    );
};

export default CreateQuiz;