import { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useAuth } from '../../context/AuthContext';
import { useTheme } from '../../context/ThemeContext';
import axios from '../../utils/axios';
import Icon from '../Icon';
import Loader from '../common/Loader';
import MaterialItem from '../item/MaterialItem';
import TareaItem from '../item/TareaItem';
import QuizItem from '../item/QuizItem';

const ItemDetails = () => {
    const { user } = useAuth();
    const { isDarkMode } = useTheme();
    const { courseId, itemType, itemId } = useParams();
    const navigate = useNavigate();
    
    const [item, setItem] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        const fetchItemDetails = async () => {
            try {
                setLoading(true);
                setError(null);
                
                let endpoint = '';
                switch (itemType) {
                    case 'material':
                        endpoint = `/api/item/${courseId}/material/${itemId}`;
                        break;
                    case 'tarea':
                        endpoint = `/api/item/${courseId}/tarea/${itemId}`;
                        break;
                    case 'quiz':
                        endpoint = `/api/item/${courseId}/quiz/${itemId}`;
                        break;
                    default:
                        setError('Tipo de elemento no válido');
                        setLoading(false);
                        return;
                }
                
                const response = await axios.get(endpoint);
                setItem(response.data);
            } catch (error) {
                console.error(`Error al cargar el ${itemType}:`, error);
                setError(error.response?.data?.message || `Error al cargar el ${itemType}`);
            } finally {
                setLoading(false);
            }
        };

        fetchItemDetails();
    }, [courseId, itemType, itemId]);

    const handleBack = () => {
        navigate(`/cursos/${courseId}`);
    };

    const handleItemUpdate = (updatedData) => {
        setItem(prev => ({
            ...prev,
            entrega: updatedData
        }));
    };

    const getItemTitle = () => {
        switch (itemType) {
            case 'material':
                return 'Material';
            case 'tarea':
                return 'Tarea';
            case 'quiz':
                return 'Quiz';
            default:
                return 'Elemento';
        }
    };

    if (loading) {
        return (
            <div className="container mt-5">
                <div className="text-center">
                    <Loader size="large" />
                    <p className="mt-3">Cargando detalles...</p>
                </div>
            </div>
        );
    }

    if (error) {
        return (
            <div className="container mt-5">
                <div className="alert alert-danger" role="alert">
                    <h4 className="alert-heading">Error</h4>
                    <p>{error}</p>
                    <hr />
                    <button className="btn btn-outline-danger" onClick={handleBack}>
                        <Icon name="dglasses" size={20} className="me-2" />
                        Volver al curso
                    </button>
                </div>
            </div>
        );
    }

    if (!item) {
        return (
            <div className="container mt-5">
                <div className="alert alert-warning" role="alert">
                    <h4 className="alert-heading">No encontrado</h4>
                    <p>El elemento solicitado no existe o no tienes acceso a él.</p>
                    <hr />
                    <button className="btn btn-outline-warning" onClick={handleBack}>
                        <Icon name="dglasses" size={20} className="me-2" />
                        Volver al curso
                    </button>
                </div>
            </div>
        );
    }

    return (
        <div className="container mt-4">
            <div className="row">
                <div className="col-12">
                    <div className={`mb-4 card ${isDarkMode ? 'bg-dark text-light' : ''}`}>
                        <div className="card-header d-flex justify-content-between align-items-center">
                            <h4 className="mb-0">
                                {getItemTitle()}: {item.titulo}
                            </h4>
                            <button className="btn btn-outline-secondary" onClick={handleBack}>
                                <Icon name="dglasses" size={20} className="me-2" />
                                Volver al curso
                            </button>
                        </div>
                        <div className="card-body">
                            <div className="row">
                                <div className="col-md-8">
                                    <div className="mb-4">
                                        <h5>Descripción</h5>
                                        <p>{item.descripcion}</p>
                                    </div>

                                    {itemType === 'material' && (
                                        <MaterialItem item={item} />
                                    )}

                                    {itemType === 'tarea' && (
                                        <TareaItem 
                                            item={item} 
                                            courseId={courseId}
                                            onUpdate={handleItemUpdate}
                                        />
                                    )}

                                    {itemType === 'quiz' && (
                                        <QuizItem item={item} courseId={courseId} />
                                    )}
                                </div>
                                <div className="col-md-4">
                                    <div className={`card ${isDarkMode ? 'bg-secondary text-light' : 'bg-light'}`}>
                                        <div className="card-body">
                                            <h5 className="card-title">Información</h5>
                                            <ul className="list-group list-group-flush">
                                                <li className={`list-group-item ${isDarkMode ? 'bg-dark text-light' : ''}`}>
                                                    <strong>Fecha de publicación:</strong><br />
                                                    {new Date(item.fechaPublicacion).toLocaleString()}
                                                </li>
                                                {(itemType === 'tarea' || itemType === 'quiz') && (
                                                    <li className={`list-group-item ${isDarkMode ? 'bg-dark text-light' : ''}`}>
                                                        <strong>Fecha límite:</strong><br />
                                                        {new Date(item.fechaLimite).toLocaleString()}
                                                    </li>
                                                )}
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default ItemDetails;