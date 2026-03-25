import { Link } from 'react-router-dom';
import TileCal3D from '../../components/detector_3d/TileCal3D';
import './home.css';

export default function Home() {
  return (
    <div style={{ padding: '2rem', minHeight: '100vh', backgroundColor: '#ffffff', color: '#333333' }}>
      
      <h1 style={{ textAlign: 'center', marginBottom: '1rem', color: '#1e3a8a', letterSpacing: '1px' }}>
        Welcome to the Tilecal Electronics Database
      </h1>
      <p style={{ textAlign: 'center', color: '#64748b', marginBottom: '2rem', fontFamily: 'monospace' }}>
        Atlas Detector Technical Data Dashboard (Proof of Concept)
      </p>
      
      <div style={{ width: '100%', height: '600px', marginBottom: '3rem', border: '1px solid #e2e8f0', borderRadius: '8px', overflow: 'hidden', boxShadow: '0 4px 20px rgba(0,0,0,0.05)' }}>
        <TileCal3D />
      </div>

      <div className="grid mt-4 text-center">
        <div className="col-12 md:col-6 mt-4 px-5">
            <span className="p-3 shadow-2 mb-3 inline-block" style={{ borderRadius: '10px' }}>
                <i className="bi bi-pci-card text-4xl text-blue-500"></i>
            </span>
            <div className="text-900 text-xl mb-3 font-medium">Equipment</div>
            <div className="text-700 line-height-3"><Link to='equipment'>Index</Link></div>
        </div>
        <div className="col-12 md:col-6 mt-4 px-5">
            <span className="p-3 shadow-2 mb-3 inline-block" style={{ borderRadius: '10px' }}>
                <i className="pi pi-file text-4xl text-blue-500"></i>
            </span>
            <div className="text-900 text-xl mb-3 font-medium">Files</div>
            <span className="text-700 line-height-3"><Link to='file'>Index</Link></span>
        </div>
      </div>

    </div>
  );
}