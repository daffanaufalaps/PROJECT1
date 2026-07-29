-- Narration templates table
CREATE TABLE narration_templates (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(100) NOT NULL,
    template TEXT NOT NULL,
    variables JSONB DEFAULT '[]',
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Insert default narration template
INSERT INTO narration_templates (name, template, variables) VALUES (
    'Default Result Narrative',
    'Berdasarkan analisis titik koordinat {latitude}° LS, {longitude}° BT, diperoleh nilai PGA (Peak Ground Acceleration) sebesar {pga}g yang setara dengan skala MMI {mmi}. Lokasi ini termasuk dalam {risk_category} dengan nilai KDS (Kategori Desain Seismik) {kds}. Parameter respons spektra desain: Ss = {ss}g, S1 = {s1}g, Fa = {fa}, Fv = {fv}, SMs = {sms}g, SM1 = {sm1}g, SDs = {sds}g, SD1 = {sd1}g.',
    '["latitude", "longitude", "pga", "mmi", "risk_category", "kds", "ss", "s1", "fa", "fv", "sms", "sm1", "sds", "sd1"]'
);