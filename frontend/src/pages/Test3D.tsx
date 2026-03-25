import TileCal3D from '../components/detector_3d/TileCal3D';

export default function Test3DPage() {
    return (
        <div className="p-4">
            <h1>TileCal 3D Proof of Concept</h1>
            <p className="mb-4 text-500">
                Arraste com o botão esquerdo para rodar, use o scroll para zoom. 
                Clique nos módulos para interagir.
            </p>
            <div style={{ border: '1px solid #333', borderRadius: '8px', overflow: 'hidden' }}>
                <TileCal3D />
            </div>
        </div>
    );
}