import { useTheme } from './ThemeContext';

export const Home = () => {
    const { isDarkMode } = useTheme();

    return (
        <div className={`container ${isDarkMode ? 'dark-mode' : ''}`}>
            <div className="row min-vh-100 align-items-center justify-content-center text-center">
                <div className="col-md-8">
                    <h1 className="display-4 mt-5 pt-5 fw-bold">Bienvenido a nuestra plataforma</h1>
                    <p className="lead mt-3">
                        Explora nuestro espacio de aprendizaje gamificado y descubre una nueva forma de aprender.
                    </p>
                    <hr className="my-4" />
                    <p className="fs-5">
                        Únete a nuestra comunidad educativa y transforma tu experiencia de aprendizaje.
                    </p>
                </div>
            </div>
        </div>
    );
};